'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileNavLinks = document.querySelectorAll('.nav-link-mobile');
    const header = document.getElementById('main-header');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            mobileMenuButton.setAttribute('aria-expanded', String(!isHidden));
            document.body.style.overflow = isHidden ? '' : 'hidden';
        });
    }

    mobileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mobileMenu) {
                mobileMenu.classList.add('hidden');
                mobileMenuButton?.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    });

    if (header) {
        const toggleHeaderGlass = () => {
            header.classList.toggle('glass-effect', window.scrollY > 50);
        };
        toggleHeaderGlass();
        window.addEventListener('scroll', toggleHeaderGlass);
    }

    const fadeSections = document.querySelectorAll('.section-fade-in');
    if (fadeSections.length) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        fadeSections.forEach(section => observer.observe(section));
    }

    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        const formSuccessMessage = document.getElementById('contact-form-success');
        const formErrorMessage = document.getElementById('contact-form-error');
        const submitButton = contactForm.querySelector('button[type="submit"]');
        const recaptchaSiteKey = (window.RECAPTCHA_SITE_KEY || '').trim();
        const recaptchaDebug = Boolean(window.RECAPTCHA_DEBUG);
        const recaptchaLog = (...args) => {
            if (recaptchaDebug) {
                console.log('[reCAPTCHA]', ...args);
            }
        };
        const recaptchaError = (...args) => {
            if (recaptchaDebug) {
                console.error('[reCAPTCHA]', ...args);
            }
        };
        const formEndpoint = contactForm.dataset.endpoint || contactForm.getAttribute('action') || 'contact-submit.php';

        const setSubmittingState = (isSubmitting) => {
            if (submitButton) {
                submitButton.disabled = isSubmitting;
                submitButton.dataset.originalText = submitButton.dataset.originalText || submitButton.textContent;
                submitButton.textContent = isSubmitting ? 'Sender...' : submitButton.dataset.originalText;
            }
        };

        const displayMessage = (type, message) => {
            if (formErrorMessage) {
                formErrorMessage.classList.add('hidden');
                formErrorMessage.textContent = '';
            }
            if (formSuccessMessage) {
                formSuccessMessage.classList.add('hidden');
            }

            if (type === 'error' && formErrorMessage) {
                formErrorMessage.textContent = message;
                formErrorMessage.classList.remove('hidden');
            }
            if (type === 'success' && formSuccessMessage) {
                formSuccessMessage.classList.remove('hidden');
            }
        };

        const fetchRecaptchaToken = async () => {
            if (!recaptchaSiteKey) {
                recaptchaLog('Site key missing, skipping token fetch');
                return '';
            }

            // Check for Enterprise API first, fallback to standard API
            const isEnterprise = typeof grecaptcha !== 'undefined' && grecaptcha.enterprise;
            const api = isEnterprise ? grecaptcha.enterprise : grecaptcha;
            recaptchaLog('Using', isEnterprise ? 'Enterprise' : 'Standard', 'reCAPTCHA API');

            if (!api) {
                recaptchaError('reCAPTCHA API not available on window');
                return '';
            }

            return new Promise(resolve => {
                try {
                    api.ready(() => {
                        recaptchaLog('Executing reCAPTCHA with action "contact"');
                        api.execute(recaptchaSiteKey, { action: 'contact' })
                            .then(token => {
                                if (!token) {
                                    recaptchaError('reCAPTCHA returned empty token');
                                } else {
                                    recaptchaLog('Token generated (length:', token.length + ')');
                                }
                                resolve(token || '');
                            })
                            .catch(error => {
                                recaptchaError('Execution error', error);
                                resolve('');
                            });
                    });
                } catch (error) {
                    recaptchaError('Initialization error', error);
                    resolve('');
                }
            });
        };

        const parseResponse = async (response) => {
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                return response.json();
            }
            const text = await response.text();
            return { status: response.ok ? 'ok' : 'error', message: text };
        };

        contactForm.addEventListener('submit', async event => {
            event.preventDefault();
            displayMessage('reset');
            setSubmittingState(true);

            try {
                const recaptchaToken = await fetchRecaptchaToken();
                const formData = new FormData(contactForm);
                if (recaptchaToken) {
                    formData.set('recaptcha_token', recaptchaToken);
                }

                const response = await fetch(formEndpoint, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const result = await parseResponse(response);

                if (response.ok && result.success === true) {
                    recaptchaLog('Submission succeeded');
                    displayMessage('success');
                    contactForm.reset();
                    // Reset reCAPTCHA for next submission
                    if (recaptchaSiteKey) {
                        const isEnterprise = typeof grecaptcha !== 'undefined' && grecaptcha.enterprise;
                        const api = isEnterprise ? grecaptcha.enterprise : grecaptcha;
                        if (api && typeof api.reset === 'function') {
                            try {
                                api.reset();
                            } catch (error) {
                                recaptchaError('Reset failed', error);
                            }
                        }
                    }
                } else {
                    const message = result.message || 'Der opstod en fejl. Prøv igen senere.';
                    recaptchaError('Submission failed', message, result);
                    displayMessage('error', message);
                }
            } catch (error) {
                recaptchaError('Unexpected submission error', error);
                displayMessage('error', 'Kunne ikke sende forespørgslen. Kontrollér din forbindelse og prøv igen.');
            } finally {
                setSubmittingState(false);
            }
        });
    }

    const heroCanvas = document.getElementById('hero-canvas');
    if (heroCanvas) {
        const ctx = heroCanvas.getContext('2d');
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@#$%^&*()_+-=[]{}|;':,./<>?".split('');
        let drops = [];

        const setupCanvas = () => {
            heroCanvas.width = window.innerWidth;
            heroCanvas.height = window.innerHeight;
            const columns = Math.floor(heroCanvas.width / 20);
            drops = Array.from({ length: columns }, () => Math.floor(Math.random() * (heroCanvas.height / 20)));
        };

        const drawDigitalRain = () => {
            ctx.fillStyle = 'rgba(16, 20, 25, 0.05)';
            ctx.fillRect(0, 0, heroCanvas.width, heroCanvas.height);

            const color = getComputedStyle(document.documentElement)
                .getPropertyValue('--digital-rain-color')
                .trim() || '#008000';

            ctx.fillStyle = color;
            ctx.font = '15px monospace';

            drops.forEach((drop, index) => {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, index * 20, drop * 20);

                if (drop * 20 > heroCanvas.height && Math.random() > 0.975) {
                    drops[index] = 0;
                }
                drops[index] = drop + 1;
            });
        };

        setupCanvas();
        let intervalId = window.setInterval(drawDigitalRain, 33);

        window.addEventListener('resize', () => {
            window.clearInterval(intervalId);
            setupCanvas();
            intervalId = window.setInterval(drawDigitalRain, 33);
        });
    }

    const hasAIConfig = typeof window.AI_CONFIG !== 'undefined';
    const geminiReady = hasAIConfig && Boolean(AI_CONFIG.GEMINI_API_KEY && AI_CONFIG.GEMINI_MODEL && AI_CONFIG.API_BASE_URL);

    const convertMarkdownToHtml = (text) => {
        let html = text
            .replace(/### (.*$)/gim, '<h3 class="text-lg font-bold mb-2 text-amber-400">$1</h3>')
            .replace(/## (.*$)/gim, '<h2 class="text-xl font-bold mb-3 text-amber-400">$1</h2>')
            .replace(/# (.*$)/gim, '<h1 class="text-2xl font-bold mb-4 text-amber-400">$1</h1>')
            .replace(/\*\*(.*?)\*\*/g, '<strong class="text-white">$1</strong>')
            .replace(/\*(.*?)\*/g, '<em class="text-gray-200">$1</em>')
            .replace(/`(.*?)`/g, '<code class="bg-gray-800 px-1 py-0.5 rounded text-amber-300 text-sm">$1</code>')
            .replace(/^\* (.*$)/gim, '<li class="ml-5 mb-2 text-gray-300">$1</li>')
            .replace(/^- (.*$)/gim, '<li class="ml-5 mb-2 text-gray-300">$1</li>')
            .replace(/\n/g, '<br>');

        html = html.replace(/(<li[^>]*>.*?<\/li>(?:\s*<br>\s*<li[^>]*>.*?<\/li>)*)/gs, '<ul class="mb-4">$1</ul>');
        html = html.replace(/<br><ul>/g, '<ul>').replace(/<\/ul><br>/g, '</ul>');
        return html;
    };

    const callGemini = async (prompt, resultElement, loaderElement, requestType = 'generic') => {
        if (!geminiReady) {
            if (loaderElement) loaderElement.classList.add('hidden');
            if (resultElement) {
                resultElement.innerHTML = '<p class="text-red-400">AI-konfiguration mangler. Kontakt administratoren.</p>';
                resultElement.classList.remove('hidden');
            }
            return;
        }

        const now = Date.now();
        const requestKey = 'gemini_requests';
        const limit = AI_CONFIG.MAX_REQUESTS_PER_MINUTE || 5;
        let requests = [];

        try {
            requests = JSON.parse(localStorage.getItem(requestKey) || '[]');
        } catch (error) {
            requests = [];
        }

        requests = requests.filter(time => now - time < 60000);
        if (requests.length >= limit) {
            if (loaderElement) loaderElement.classList.add('hidden');
            if (resultElement) {
                resultElement.innerHTML = '<p class="text-yellow-400">For mange forespørgsler. Vent et øjeblik og prøv igen.</p>';
                resultElement.classList.remove('hidden');
            }
            return;
        }

        const payload = {
            contents: [
                {
                    role: 'user',
                    parts: [{ text: prompt }]
                }
            ]
        };

        const apiUrl = `${AI_CONFIG.API_BASE_URL}/${AI_CONFIG.GEMINI_MODEL}:generateContent?key=${AI_CONFIG.GEMINI_API_KEY}`;
        const controller = new AbortController();
        const timeoutId = window.setTimeout(() => controller.abort(), AI_CONFIG.REQUEST_TIMEOUT || 15000);

        try {
            if (AI_CONFIG.LOG_REQUESTS) {
                console.info('Gemini request', { requestType, timestamp: now });
            }

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                signal: controller.signal
            });

            window.clearTimeout(timeoutId);

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`API error ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            requests.push(now);
            localStorage.setItem(requestKey, JSON.stringify(requests));

            let text = 'Kunne ikke generere et svar. Prøv venligst igen.';
            if (result?.candidates?.[0]?.content?.parts?.[0]?.text) {
                text = result.candidates[0].content.parts[0].text;
            }

            if (resultElement) {
                resultElement.innerHTML = convertMarkdownToHtml(text);
                resultElement.classList.remove('hidden');
            }
        } catch (error) {
            if (AI_CONFIG.LOG_REQUESTS) {
                console.error('Gemini error', error);
            }
            const fallbackMessage = error.name === 'AbortError'
                ? 'Forespørgslen tog for lang tid – prøv igen.'
                : 'Der opstod en fejl under kommunikation med AI-assistenten.';
            if (resultElement) {
                resultElement.innerHTML = `<p class="text-red-400">${fallbackMessage}</p>`;
                resultElement.classList.remove('hidden');
            }
        } finally {
            if (loaderElement) loaderElement.classList.add('hidden');
        }
    };

    const quickAssessmentOutputEl = document.getElementById('quick-assessment-output');
    const quickAssessmentBtn = document.getElementById('quick-assessment-btn');
    if (quickAssessmentBtn) {
        const quickAssessmentInput = document.getElementById('quick-assessment');
        quickAssessmentBtn.addEventListener('click', async () => {
            if (!quickAssessmentInput?.value.trim()) {
                quickAssessmentInput?.classList.add('border-red-500');
                setTimeout(() => quickAssessmentInput?.classList.remove('border-red-500'), 2500);
                return;
            }

            if (quickAssessmentOutputEl) {
                quickAssessmentOutputEl.classList.remove('hidden');
                quickAssessmentOutputEl.innerHTML = '<div class="flex flex-col items-center"><div class="spinner"></div><p class="mt-4 text-gray-300">Analyserer din situation...</p></div>';
            }

            const prompt = `Du er strategisk sikkerhedsrådgiver for Blackbox EYE™. Evaluer følgende udfordring og returnér tre korte afsnit: 1) Primær trussel, 2) Hurtig gevinst, 3) Foreslået Blackbox-modul. Brug et professionelt, roligt danske sprog. Kundens beskrivelse: "${quickAssessmentInput.value.trim()}".`;
            await callGemini(prompt, quickAssessmentOutputEl, null, 'quick-assessment');
        });
    }

    const geminiModal = document.getElementById('gemini-modal');
    const geminiTriggerBtns = document.querySelectorAll('.gemini-trigger-btn');
    if (geminiModal && geminiTriggerBtns.length) {
        const closeModalBtn = document.getElementById('close-modal-btn');
        const modalLoader = document.getElementById('modal-loader');
        const modalResult = document.getElementById('modal-result');
        const modalContent = document.getElementById('modal-content');

        const showModal = () => {
            geminiModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        const hideModal = () => {
            geminiModal.classList.add('hidden');
            document.body.style.overflow = '';
        };

        geminiTriggerBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const moduleName = btn.dataset.module;
                let prompt;
                if (moduleName === 'PVE') {
                    prompt = "Beskriv et realistisk cybersecurity-trusselsscenarie for en mellemstor dansk virksomhed, som Blackbox EYE's 'Penetration & Vulnerability Engine' ville opdage og forhindre. Forklar kort, hvordan en uopdaget sårbarhed i deres webshop-software kunne udnyttes af en hacker til at stjæle kundedata. Hold sproget letforståeligt for en ikke-teknisk direktør. Start med en fængende overskrift i markdown.";
                } else if (moduleName === 'GreyEYE') {
                    prompt = "Beskriv et realistisk insider-trusselsscenarie i en dansk kommune, som Blackbox EYE's 'GreyEYE' modul ville opdage. Scenariet skal involvere en medarbejder, der forsøger at eksfiltrere følsomme borgerdata over en længere periode ved at tilgå filservere uden for normal arbejdstid. Forklar hvordan GreyEYE's anomali-detektion ville reagere. Hold sproget letforståeligt. Start med en fængende overskrift i markdown.";
                } else {
                    prompt = 'Beskriv et generelt cybersecurity-trusselsscenarie til Blackbox EYE™.';
                }

                showModal();
                if (modalLoader) modalLoader.classList.remove('hidden');
                if (modalResult) {
                    modalResult.innerHTML = '';
                    modalResult.classList.add('hidden');
                }
                await callGemini(prompt, modalResult, modalLoader, `gemini-modal-${moduleName || 'generic'}`);
            });
        });

        closeModalBtn?.addEventListener('click', hideModal);
        geminiModal.addEventListener('click', event => {
            if (event.target === geminiModal) {
                hideModal();
            }
        });
        modalContent?.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                hideModal();
            }
        });
    }

    const recommendationBtn = document.getElementById('get-recommendation-btn');
    if (recommendationBtn) {
        const recommendationContainer = document.getElementById('recommendation-result-container');
        const recommendationLoader = document.getElementById('recommendation-loader');
        const recommendationResult = document.getElementById('recommendation-result');
        recommendationBtn.addEventListener('click', async () => {
            const industrySelect = document.getElementById('industry-select');
            const employeeCountInput = document.getElementById('employee-count');
            const industry = industrySelect && 'value' in industrySelect ? industrySelect.value : 'Ukendt branche';
            const employees = employeeCountInput && 'value' in employeeCountInput ? employeeCountInput.value : '0';

            recommendationContainer?.classList.remove('hidden');
            recommendationLoader?.classList.remove('hidden');
            if (recommendationResult) {
                recommendationResult.classList.add('hidden');
                recommendationResult.innerHTML = '';
            }

            const prompt = `Du er AI-sikkerhedsrådgiver for Blackbox EYE™. En potentiel kunde fra '${industry}'-branchen med ${employees} ansatte har bedt om en analyse. Gør følgende i markdown: 1) Top 3 trusler, 2) Økonomisk risiko-estimat, 3) Anbefalet pakke (Standard, Premium eller Enterprise) med begrundelse.`;
            await callGemini(prompt, recommendationResult, recommendationLoader, 'pricing-advice');
        });
    }

    const analyzeCaseBtn = document.getElementById('analyze-case-btn');
    if (analyzeCaseBtn) {
        const caseInput = document.getElementById('case-input');
        const caseContainer = document.getElementById('case-analysis-container');
        const caseLoader = document.getElementById('case-analysis-loader');
        const caseResult = document.getElementById('case-analysis-result');

        analyzeCaseBtn.addEventListener('click', async () => {
            if (!caseInput?.value.trim()) {
                caseInput?.classList.add('border-red-500');
                setTimeout(() => caseInput?.classList.remove('border-red-500'), 2500);
                return;
            }

            caseContainer?.classList.remove('hidden');
            caseLoader?.classList.remove('hidden');
            if (caseResult) {
                caseResult.classList.add('hidden');
                caseResult.innerHTML = '';
            }

            const prompt = `Du er ekspert AI-sikkerhedskonsulent for Blackbox EYE™. Kunden beskriver: "${caseInput.value.trim()}". Returnér i markdown: 1) Sammenlign med én af vores cases (kommune, ejendomsselskab, vagtselskab) og forklar hvorfor. 2) Anbefal 1-2 relevante moduler (PVE, GreyEYE, ID-Matrix, AUT) med begrundelser. 3) Foreslå næste skridt (f.eks. demo, workshop).`;
            await callGemini(prompt, caseResult, caseLoader, 'case-analysis');
        });
    }

    if (quickAssessmentOutputEl && !hasAIConfig) {
        quickAssessmentOutputEl.classList.remove('hidden');
        quickAssessmentOutputEl.innerHTML = '<p class="text-red-400">AI-konfiguration mangler. Kontakt administratoren.</p>';
    }

    const alphaContainer = document.getElementById('alphabot-container');
    const alphaToggleBtn = document.getElementById('alphabot-toggle-btn');
    const alphaCloseBtn = document.getElementById('alphabot-close-btn');
    if (alphaContainer && alphaToggleBtn && hasAIConfig && geminiReady) {
        const messagesDiv = document.getElementById('alphabot-messages');
        const inputEl = document.getElementById('alphabot-input');
        const sendBtn = document.getElementById('alphabot-send-btn');
        const sendText = document.getElementById('send-text');
        const sendLoader = document.getElementById('send-loader');

        if (messagesDiv && inputEl && sendBtn && sendText && sendLoader) {
            const conversation = [
                { role: 'user', parts: [{ text: AI_CONFIG.ALPHABOT_SYSTEM_PROMPT || '' }] },
                { role: 'model', parts: [{ text: 'Forstået. Jeg er klar til at assistere med sikkerhedsrelaterede spørgsmål og analyser.' }] }
            ];
            let isProcessing = false;

            const appendMessage = (role, text) => {
                const wrapper = document.createElement('div');
                wrapper.className = `chat-message ${role}`;
                const message = document.createElement('div');
                message.className = 'message-text';
                if (role === 'bot') {
                    message.innerHTML = convertMarkdownToHtml(text);
                } else {
                    message.textContent = text;
                }
                wrapper.appendChild(message);
                messagesDiv.appendChild(wrapper);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            };

            const setProcessing = (state) => {
                isProcessing = state;
                if (sendBtn && inputEl) {
                    sendBtn.disabled = state || !inputEl.value.trim();
                    inputEl.disabled = state;
                }
                if (state) {
                    sendText.classList.add('hidden');
                    sendLoader.classList.remove('hidden');
                } else {
                    sendText.classList.remove('hidden');
                    sendLoader.classList.add('hidden');
                }
            };

            const callAlphaBot = async () => {
                const apiUrl = `${AI_CONFIG.API_BASE_URL}/${AI_CONFIG.GEMINI_MODEL}:generateContent?key=${AI_CONFIG.GEMINI_API_KEY}`;
                const controller = new AbortController();
                const timeoutId = window.setTimeout(() => controller.abort(), AI_CONFIG.REQUEST_TIMEOUT || 15000);
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ contents: conversation }),
                        signal: controller.signal
                    });
                    window.clearTimeout(timeoutId);
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`AlphaBot API error: ${response.status} - ${errorText}`);
                    }
                    const result = await response.json();
                    const reply = result?.candidates?.[0]?.content?.parts?.[0]?.text || 'Undskyld, jeg kunne ikke generere et svar.';
                    appendMessage('bot', reply.trim());
                    conversation.push({ role: 'model', parts: [{ text: reply.trim() }] });
                } catch (error) {
                    const fallback = error.name === 'AbortError'
                        ? 'Forespørgslen tog for lang tid – prøv igen.'
                        : 'Der opstod en fejl under forbindelsen til AlphaBot. Prøv igen senere.';
                    appendMessage('bot', fallback);
                } finally {
                    setProcessing(false);
                }
            };

            const sendMessage = () => {
                if (isProcessing) return;
                const value = inputEl.value.trim();
                if (!value) return;
                appendMessage('user', value);
                conversation.push({ role: 'user', parts: [{ text: value }] });
                inputEl.value = '';
                setProcessing(true);
                void callAlphaBot();
            };

            alphaToggleBtn.addEventListener('click', () => {
                alphaContainer.classList.toggle('open');
                if (alphaContainer.classList.contains('open')) {
                    inputEl.focus();
                }
            });

            alphaCloseBtn?.addEventListener('click', () => {
                alphaContainer.classList.remove('open');
            });

            sendBtn.addEventListener('click', sendMessage);
            inputEl.addEventListener('keydown', event => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendMessage();
                }
            });

            inputEl.addEventListener('input', () => {
                if (!isProcessing) {
                    sendBtn.disabled = !inputEl.value.trim();
                }
            });
        }
    }
});
