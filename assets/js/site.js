'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
    const mobileNavLinks = document.querySelectorAll('.nav-link-mobile');
    const header = document.getElementById('main-header');

    // Mobile menu functionality with improved UX
    if (mobileMenuButton && mobileMenu && mobileMenuOverlay) {
        let lastFocusedElement = null;

        const openMobileMenu = () => {
            lastFocusedElement = document.activeElement;
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            mobileMenuButton.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';

            // Focus first link after animation
            setTimeout(() => {
                const firstLink = mobileMenu.querySelector('a');
                if (firstLink) firstLink.focus();
            }, 100);
        };

        const closeMobileMenu = () => {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';

            // Restore focus
            if (lastFocusedElement) {
                lastFocusedElement.focus();
            }
        };

        // Toggle menu on button click
        mobileMenuButton.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.contains('active');
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        // Close button
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        // Close on overlay click
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });

        // Close on navigation click
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', () => {
                closeMobileMenu();
            });
        });
    }

    if (header) {
        const toggleHeaderGlass = () => {
            header.classList.toggle('glass-effect', window.scrollY > 50);
        };
        toggleHeaderGlass();
        window.addEventListener('scroll', toggleHeaderGlass);
    }

    // Sticky CTA visibility on scroll
    const stickyCTA = document.getElementById('sticky-cta');
    if (stickyCTA) {
        const toggleStickyCTA = () => {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            // Show after scrolling 50% of viewport height
            // Hide when near bottom (within 200px of footer)
            const shouldShow = scrollPosition > windowHeight * 0.5 &&
                               scrollPosition < documentHeight - windowHeight - 200;

            stickyCTA.classList.toggle('visible', shouldShow);
        };

        toggleStickyCTA();
        window.addEventListener('scroll', toggleStickyCTA);
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
        const recaptchaHasClients = () => {
            if (typeof grecaptcha === 'undefined' || typeof grecaptcha.reset !== 'function') {
                return false;
            }
            const cfg = grecaptcha.___grecaptcha_cfg;
            if (!cfg || !cfg.clients) {
                return false;
            }
            return Object.keys(cfg.clients).length > 0;
        };
        const formEndpoint = contactForm.dataset.endpoint || contactForm.getAttribute('action') || 'contact-submit.php';

        // Log initial configuration
        if (recaptchaDebug) {
            console.log('[Contact Form] Configuration:', {
                endpoint: formEndpoint,
                recaptchaSiteKey: recaptchaSiteKey ? recaptchaSiteKey.substring(0, 20) + '...' : '[EMPTY]',
                grecaptchaLoaded: typeof grecaptcha !== 'undefined',
                enterpriseAvailable: typeof grecaptcha !== 'undefined' && grecaptcha.enterprise !== undefined,
                debug: recaptchaDebug
            });
        }

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

        const showAILoadingState = (container, message = 'Analyserer...') => {
            container.innerHTML = `
                <div class="ai-loading-container">
                    <div class="ai-spinner"></div>
                    <p class="ai-loading-text">${message}</p>
                </div>
            `;
            container.classList.remove('hidden');
        };

        const showSkeletonLoader = (container) => {
            container.innerHTML = `
                <div class="skeleton-heading skeleton"></div>
                <div class="skeleton-line skeleton"></div>
                <div class="skeleton-line skeleton"></div>
                <div class="skeleton-line skeleton short"></div>
            `;
            container.classList.remove('hidden');
        };

        const fetchRecaptchaToken = async () => {
            if (!recaptchaSiteKey) {
                recaptchaLog('Site key missing, skipping token fetch');
                return '';
            }

            // Check if grecaptcha is loaded
            if (typeof grecaptcha === 'undefined') {
                recaptchaError('RECAPTCHA FRONTEND ERROR: grecaptcha not loaded - script may be blocked or site key invalid');
                return '';
            }

            // Check for grecaptcha API (Standard v3)
            const api = typeof grecaptcha !== 'undefined' ? grecaptcha : null;
            recaptchaLog('Using Standard reCAPTCHA v3 API');

            if (!api) {
                recaptchaError('RECAPTCHA FRONTEND ERROR: API not available');
                return '';
            }

            return new Promise(resolve => {
                try {
                    const readyCheck = setTimeout(() => {
                        recaptchaError('RECAPTCHA FRONTEND ERROR: ready() timeout - site key may be invalid');
                        resolve('');
                    }, 5000);

                    api.ready(() => {
                        clearTimeout(readyCheck);
                        recaptchaLog('Executing reCAPTCHA with action "contact"');
                        api.execute(recaptchaSiteKey, { action: 'contact' })
                            .then(token => {
                                if (!token) {
                                    recaptchaError('RECAPTCHA FRONTEND ERROR: Empty token returned');
                                } else {
                                    recaptchaLog('Token generated (length:', token.length + ')');
                                }
                                resolve(token || '');
                            })
                            .catch(error => {
                                recaptchaError('RECAPTCHA FRONTEND ERROR: Execute failed -', error.message || error);
                                resolve('');
                            });
                    });
                } catch (error) {
                    recaptchaError('RECAPTCHA FRONTEND ERROR: Initialization failed -', error.message || error);
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

            recaptchaLog('Contact form submit started');

            try {
                const recaptchaToken = await fetchRecaptchaToken();
                const formData = new FormData(contactForm);
                if (recaptchaToken) {
                    formData.set('recaptcha_token', recaptchaToken);
                }

                recaptchaLog('Sending POST to:', formEndpoint);
                recaptchaLog('Form data keys:', Array.from(formData.keys()));

                const response = await fetch(formEndpoint, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                recaptchaLog('Response status:', response.status, response.statusText);
                recaptchaLog('Response headers:', Object.fromEntries(response.headers.entries()));

                const result = await parseResponse(response);
                recaptchaLog('Parsed response:', result);

                if (response.ok && result.success === true) {
                    recaptchaLog('Submission succeeded');
                    displayMessage('success');
                    contactForm.reset();
                    // Reset reCAPTCHA for next submission
                    if (recaptchaSiteKey && typeof grecaptcha !== 'undefined' && recaptchaHasClients()) {
                        try {
                            grecaptcha.reset();
                        } catch (error) {
                            recaptchaError('Reset failed', error);
                        }
                    } else if (recaptchaDebug) {
                        recaptchaLog('Skipping grecaptcha.reset() – no clients registered (expected for v3).');
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
        let animationId = null;
        let isAnimating = false;

        // Check if user prefers reduced motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion) {
            heroCanvas.style.display = 'none';
        } else {
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

                if (isAnimating) {
                    animationId = requestAnimationFrame(drawDigitalRain);
                }
            };

            const startAnimation = () => {
                if (!isAnimating) {
                    isAnimating = true;
                    drawDigitalRain();
                }
            };

            const stopAnimation = () => {
                isAnimating = false;
                if (animationId) {
                    cancelAnimationFrame(animationId);
                    animationId = null;
                }
            };

            setupCanvas();
            startAnimation();

            // Pause animation when tab is hidden (performance optimization)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAnimation();
                } else {
                    startAnimation();
                }
            });

            // Restart animation on resize
            window.addEventListener('resize', () => {
                stopAnimation();
                setupCanvas();
                startAnimation();
            });
        }
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

        // Show improved loading state
        if (resultElement) {
            showAILoadingState(resultElement, 'AI-assistenten analyserer din forespørgsel...');
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
                showAILoadingState(quickAssessmentOutputEl, 'Analyserer din sikkerhedssituation...');
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

        let lastFocusedElement = null;

        const showModal = () => {
            lastFocusedElement = document.activeElement;
            geminiModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Set focus to close button and setup focus trap
            setTimeout(() => {
                closeModalBtn?.focus();
                setupFocusTrap(modalContent);
            }, 100);
        };

        const hideModal = () => {
            geminiModal.classList.add('hidden');
            document.body.style.overflow = '';

            // Restore focus to trigger element
            if (lastFocusedElement) {
                lastFocusedElement.focus();
            }
        };

        const setupFocusTrap = (container) => {
            if (!container) return;

            const focusableElements = container.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );

            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            const handleTabKey = (e) => {
                if (e.key !== 'Tab') return;

                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            };

            container.addEventListener('keydown', handleTabKey);
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
                if (modalLoader && modalResult) {
                    showAILoadingState(modalLoader);
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

        // ESC key to close modal
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !geminiModal.classList.contains('hidden')) {
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
            if (recommendationLoader) showSkeletonLoader(recommendationLoader);
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
            if (caseLoader) showSkeletonLoader(caseLoader);
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

            const openAlphaBot = () => {
                alphaContainer.classList.add('open');
                alphaToggleBtn.setAttribute('aria-expanded', 'true');
                inputEl.focus();
            };

            const closeAlphaBot = (focusToggle = true) => {
                alphaContainer.classList.remove('open');
                alphaToggleBtn.setAttribute('aria-expanded', 'false');
                if (focusToggle) {
                    alphaToggleBtn.focus();
                }
            };

            alphaToggleBtn.addEventListener('click', () => {
                if (alphaContainer.classList.contains('open')) {
                    closeAlphaBot(false);
                } else {
                    openAlphaBot();
                }
            });

            alphaCloseBtn?.addEventListener('click', () => {
                closeAlphaBot();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && alphaContainer.classList.contains('open')) {
                    event.preventDefault();
                    closeAlphaBot();
                }
            });

            document.addEventListener('click', (event) => {
                if (!alphaContainer.contains(event.target) && alphaContainer.classList.contains('open')) {
                    closeAlphaBot(false);
                }
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
