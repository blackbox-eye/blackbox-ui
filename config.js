// AI Configuration for Blackbox EYE™
// Dette er en placeholder - i produktion bør API-nøgler gemmes sikkert på serveren
const AI_CONFIG = {
    // Do NOT keep API keys in client-side files. Use server-side environment variables
    // or GitHub Actions secrets and proxy requests through the backend.
    // In production set the key in your hosting environment and access it server-side.
    GEMINI_API_KEY: "REPLACE_WITH_SERVER_SECRET",
    GEMINI_MODEL: "gemini-2.0-flash",
    API_BASE_URL: "https://generativelanguage.googleapis.com/v1beta/models",
    
    // Rate limiting
    MAX_REQUESTS_PER_MINUTE: 20,
    REQUEST_TIMEOUT: 30000, // 30 sekunder
    
    // System prompts
    ALPHABOT_SYSTEM_PROMPT: `Du er GreyEYE AlphaBot, en AI-sikkerhedsassistent for Blackbox EYE™. 
    Du hjælper med cybersikkerhed, trusselsvurderinger og tekniske spørgsmål. 
    Vær professionel, præcis og hjælpsom. Svar på dansk med mindre andet anmodes.
    Du har ekspertise inden for penetrationstests, OSINT, incident response og cybersecurity.
    
    VIGTIG FORMATERING: Brug kun let markdown formatering:
    - Brug ** for vigtige ord og begreber
    - Brug * for lister
    - Brug ## for overskrifter hvis nødvendigt
    - Hold dine svar strukturerede og letlæselige
    - Undgå kompleks formatering`,
    
    THREAT_SCENARIO_PROMPT_PREFIX: `Du er en cybersikkerhedsekspert for Blackbox EYE™. `,
    
    // Sikkerhedsindstillinger
    CONTENT_FILTERING: true,
    LOG_REQUESTS: true
};

// Eksporter konfiguration til global scope
window.AI_CONFIG = AI_CONFIG;
