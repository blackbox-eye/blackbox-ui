// AI Configuration for Blackbox EYE™
// Dette er en placeholder - i produktion bør API-nøgler gemmes sikkert på serveren
const AI_CONFIG = {
    // Midlertidig løsning - flyttes til server-side miljø i produktion
    GEMINI_API_KEY: "AIzaSyAmX6gTIN2tIiQ99HM8LopGsD-jmUENYLw",
    GEMINI_MODEL: "gemini-2.0-flash",
    API_BASE_URL: "https://generativelanguage.googleapis.com/v1beta/models",
    
    // Rate limiting
    MAX_REQUESTS_PER_MINUTE: 20,
    REQUEST_TIMEOUT: 30000, // 30 sekunder
    
    // System prompts
    ALPHABOT_SYSTEM_PROMPT: `Du er GreyEYE AlphaBot, en AI-sikkerhedsassistent for Blackbox EYE™. 
    Du hjælper med cybersikkerhed, trusselsvurderinger og tekniske spørgsmål. 
    Vær professionel, præcis og hjælpsom. Svar på dansk med mindre andet anmodes.
    Du har ekspertise inden for penetrationstests, OSINT, incident response og cybersecurity.`,
    
    THREAT_SCENARIO_PROMPT_PREFIX: `Du er en cybersikkerhedsekspert for Blackbox EYE™. `,
    
    // Sikkerhedsindstillinger
    CONTENT_FILTERING: true,
    LOG_REQUESTS: true
};

// Eksporter konfiguration til global scope
window.AI_CONFIG = AI_CONFIG;
