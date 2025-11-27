<?php
/**
 * Request Access Component
 * 
 * Sektion til at anmode om adgang, inkl. modal dialog
 * Bruges på login-siden for brugere uden adgang
 */
?>
<section class="request-access" aria-labelledby="request-access-heading">
    <h2 id="request-access-heading" class="sr-only">Anmodning om adgang</h2>
    <p class="request-access__text">
        Har du brug for operatør-login? 
        <strong>Anmod om adgang</strong> og modtag en tidsbegrænset sikker invitation.
    </p>
    <div class="request-access__actions">
        <button type="button" 
                id="requestAccessInit" 
                class="request-access__btn"
                aria-haspopup="dialog" 
                aria-controls="requestAccessDialog">
            Anmod om adgang
        </button>
        <a href="mailto:ops@blackbox.codes?subject=GreyEYE%20Access%20Request" 
           class="request-access__link">
            Eller kontakt GreyEYE sikkerhedsdesk direkte
        </a>
    </div>
    <p class="request-access__note">
        Alle forespørgsler verificeres manuelt. Autoriserede brugere modtager et krypteret link og multi-faktor onboarding.
    </p>
</section>

<!-- Request Access Modal -->
<div class="request-modal-overlay" id="requestAccessOverlay" role="presentation">
    <div class="request-modal" 
         role="dialog" 
         id="requestAccessDialog" 
         aria-modal="true" 
         aria-labelledby="requestModalTitle">
        
        <button type="button" 
                class="request-modal__close" 
                id="requestAccessClose" 
                aria-label="Luk dialog">
            &times;
        </button>
        
        <h2 id="requestModalTitle" class="request-modal__title">
            Anmod om sikker adgang
        </h2>
        
        <p class="request-modal__description">
            Indsend kontaktoplysninger og operationelt scope. 
            Vores sikkerhedsteam udsteder et unikt onboarding-link via krypteret e-mail (PGP/GPG) inden for 24 timer.
        </p>
        
        <form id="requestAccessForm" class="request-modal__form">
            <label class="request-modal__label">
                Sikker e-mail (PGP eller virksomhed)
                <input type="email" 
                       name="secureEmail" 
                       id="requestEmail" 
                       placeholder="navn@domæne.com" 
                       required 
                       autocomplete="email"
                       class="request-modal__input">
            </label>
            
            <label class="request-modal__label">
                Organisation / Titel
                <input type="text" 
                       name="org" 
                       id="requestOrg" 
                       placeholder="Virksomhed, rolle" 
                       required
                       class="request-modal__input">
            </label>
            
            <label class="request-modal__label">
                Operationelt scope & begrundelse
                <textarea name="scope" 
                          id="requestScope" 
                          placeholder="Kort beskrivelse af hvorfor adgang er nødvendig" 
                          required
                          class="request-modal__textarea"></textarea>
            </label>
            
            <div class="request-modal__actions">
                <button type="button" 
                        id="requestAccessCancel"
                        class="request-modal__btn request-modal__btn--secondary">
                    Annuller
                </button>
                <button type="submit" 
                        id="requestAccessSubmit"
                        class="request-modal__btn request-modal__btn--primary">
                    Send anmodning
                </button>
            </div>
            
            <p class="request-modal__status" 
               id="requestAccessStatus" 
               role="status" 
               aria-live="polite"></p>
        </form>
    </div>
</div>
