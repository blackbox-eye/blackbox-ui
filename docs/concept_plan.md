# Strategisk forretnings- og konceptplan for Blackbox EYE – Blackbox EYE  
*version 3.0 – 19. november 2025*

## 1. Platformens formål og ambition

Blackbox EYE (rebrandet til **Blackbox EYE**) er en **AI‑drevet sikkerhedsplatform**, der kombinerer automatiserede moduler med menneskelig ekspertise.  Rapporterne beskriver, at formålet er at gøre **usynlige trusler synlige** og levere en løsning, hvor **Blackbox EYE‑assistenten** og **PVE‑modulet** sørger for kontinuerlig pentest og real‑time overvågning, mens dedikerede **Recon, Red, Stealth og Blue Teams** styrker den menneskelige dimension【838777347905745†L20-L32】.  Platformen skal fungere både som **SaaS‑løsning** og som en **dedikeret enterprise‑løsning** for offentlige og private organisationer【798846826910414†L15-L22】.  

Visionen er at skabe en digital *supersoldat*, hvor AI orkestrerer automatiseret detektion, sårbarhedstest, adgangsstyring og træning, men hvor mennesker stadig spiller en central rolle i analyse og incident‑håndtering【838777347905745†L20-L32】.  Den nye strategi udvider platformen med en **MVP‑version** til mindre virksomheder og startups samt mere avancerede pakker til store virksomheder og offentlig sektor【798846826910414†L15-L22】.  

## 2. Pakker og prisstruktur

Platformen tilbydes i to niveauer af pakker: **MVP‑segmentet** (basis for SMV’er) og **Premium‑segmentet** (Standard, Premium, Enterprise).  Priserne nedenfor er *fastsat* og må ikke ændres; fokus er at udvikle moduler, funktioner og services, der skaber værdi inden for rammerne.

### MVP‑pakker (introduktion til Blackbox EYE)

| Pakke | Pris (ex. moms) | Målgruppe | Hovedfunktioner (uddrag) |
|---|---|---|---|
| **MVP‑Basis** | **1.799 DKK /md** | SMV’er og startups med begrænsede ressourcer | Blackbox EYE AI‑assistent, PVE (grundversion), ID‑Matrix (core), AUT træningsmodul, simpel web/app‑portal og chatbot【798846826910414†L15-L37】. |
| **MVP‑Pro** | **3.499 DKK /md** | SMV’er med behov for udvidet beskyttelse | Alt i Basis, men med udvidet pentest, automatiseret awareness‑træning og hurtigere support. |
| **MVP‑Premium** | **5.999 DKK /md** | Voksende SMV’er eller større virksomheder, der vil teste AI‑sikkerhed | Alt i Pro, plus avancerede rapporter, adgang til sandbox‑miljø og VIP‑support. |

### Premium‑pakker (fulde Blackbox EYE‑moduler)

| Pakke | Pris (ex. moms) | Målgruppe | Inkluderede funktioner |
|---|---|---|---|
| **Standard** | **9.900 DKK /md** | Mindre virksomheder, der ønsker basal cybersikkerhed uden fuldt SOC | Blackbox EYE AI‑assistent (24/7 overvågning), ID‑Matrix adgangskontrol og e‑mail‑support【798846826910414†L90-L104】. |
| **Premium** | **18.900 DKK /md** | Organisationer med højere risikoniveau og behov for træning | Alt i Standard + PVE (automatisk pentest), AUT‑modul, prioriteret support【798846826910414†L116-L124】. |
| **Enterprise** | **39.900 DKK /md (eller efter aftale)** | Store organisationer/offentlig sektor med kritisk infrastruktur | Alt i Premium + Operational Bridge (integration med kundens SOC/SIEM), Live Command Center, adgang til specialteams (Recon, Red, Stealth, Blue Teams) og 24/7 VIP‑support【798846826910414†L137-L152】. |

*Bemærk:* de gamle priser fra tidligere rapporter (899/1.499/1.799 osv.) fungerer som reference til de funktioner, der skal implementeres.  Den nye prisstruktur for version 3.0 er ovenstående, og koncepter og moduler udvikles ud fra disse prisniveauer.

## 3. Centrale moduler og deres funktioner

Platformen består af flere specialiserede moduler, der tilsammen dækker hele sikkerhedsspektret.  Nedenstående beskrivelser er baseret på rapporterne【333464687094006†L175-L219】:

### 3.1 Blackbox EYE AI‑assistent

* **24/7 overvågning:** AI‑assistenten analyserer logs, netværksdata og brugeradfærd i realtid for at identificere anomalier og insider‑trusler【333464687094006†L199-L210】.
* **Global trusselsintelligens:** Den korrelerer lokale hændelser med global threat‑intel for at opdage nye trusler tidligt【333464687094006†L199-L210】.
* **Semi‑autonom respons:** Assistenten kan isolere kompromitterede enheder eller udløse playbooks i Live Command Center, hvilket minimerer reaktionstiden【333464687094006†L260-L274】.

### 3.2 Penetration & Vulnerability Engine (PVE)

* **Automatiseret pentest og sårbarhedsscanning:** Modulet simulerer kontinuerligt avancerede angreb for at finde svagheder før hackere gør【333464687094006†L175-L185】.
* **AI‑drevet risikovurdering:** PVE anvender AI til at prioritere fund og generere rapporter med konkrete anbefalinger【333464687094006†L175-L185】.
* **Nyt navn:** Audit‑rapporten anbefaler at omdøbe PVE for at undgå forveksling med Proxmox VE【838777347905745†L942-L949】.  Et forslag kunne være **Vulnerability Hunter** eller **Offensive AI Pentester**.

### 3.3 ID‑Matrix

* **Granulær identitets‑ og adgangskontrol:** Implementerer role‑based access control (RBAC) og multifaktor‑autentificering【333464687094006†L212-L218】.
* **Anomalidetektion:** Overvåger aktive sessioner og opdager unormale adgangsmønstre i realtid【333464687094006†L212-L218】.
* **Compliance:** Understøtter principper som “Need‑to‑Know” og “Least Privilege”; logning hjælper med at opfylde GDPR/NIS2【333464687094006†L284-L324】.

### 3.4 AUT – Awareness & Training

* **Scenarie‑baseret træning:** AI‑drevet træningsmodul, der sender simulerede phishing‑emails og leverer interaktiv feedback【333464687094006†L223-L233】.
* **Adaptiv læring:** Tilpasser sværhedsgrad og indhold til den enkelte brugers rolle og tidligere resultater【333464687094006†L223-L233】.
* **Gamification:** Engagerer medarbejdere gennem game‑elementer og gør dem til en aktiv forsvarslinje【333464687094006†L223-L235】.

### 3.5 Operational Bridge

* **Integration med eksisterende SOC/SIEM:** Modulet forbinder Blackbox EYE, PVE og andre moduler med kundens eksisterende værktøjer (ServiceNow, Splunk osv.)【333464687094006†L236-L259】.
* **Bidirectional eventbus:** Sender alarmer til eksterne systemer og kan reagere på input fra fysiske sensorer【333464687094006†L236-L259】.
* **Sømløs drift:** Incident‑teams kan arbejde i deres velkendte systemer, mens AI‑platformen enriches workflowet i baggrunden【333464687094006†L236-L259】.

### 3.6 Live Command Center

* **Situationsrum i realtid:** Dashboard med netværksindikatorer, alarmskema, PVE‑rapporter og træningsstatus【333464687094006†L260-L277】.
* **Automatiserede playbooks:** Forhåndsdefinerede responsplaner kan udløses manuelt eller af AI; eksempelvis isolation af malware‑ramte enheder【333464687094006†L260-L272】.
* **Koordinering af specialteams:** Giver overblik over aktiviteterne for Recon, Red, Stealth og Blue Teams samt incident‑håndtering【333464687094006†L260-L277】.

## 4. Nye funktioner og integrationer

Audit‑rapporterne identificerer en række kritiske gaps og anbefalinger, som bør indarbejdes i version 3.0 for at levere en verdensklasse‑oplevelse:

1. **Rebranding & domæneskift:** Domænet `.codes` kolliderer med AI‑kodningsværktøjer, og navnet *Blackbox* signalerer uigennemsigtighed【838777347905745†L20-L48】.  Vælg et domæne som `.ai` eller `.security` og et mere transparent navn (fx *PrismEYE* eller *Glassbox*)【838777347905745†L168-L173】.
2. **Åbn for søgemaskiner:** Robots.txt blokerer bots; fjern blokering, implementér XML‑sitemap og schema.org markup【838777347905745†L168-L175】.
3. **Lead‑capture & demo‑booking:** Integrér et kalender‑booking‑flow (fx Calendly) og multi‑step formularer, så interessenter kan booke en demo eller få en gratis AI‑pentest【838777347905745†L176-L178】.
4. **Trust signals:** Tilføj teamprofiler, advisory board, certificeringer (ISO 27001, SOC 2), kundelogoer, partnerlogos og antal opdagede sårbarheder【838777347905745†L729-L734】.  Darktrace og SentinelOne viser badges og kundenumre【838777347905745†L706-L788】 – Blackbox EYE bør gøre det samme.
5. **Content engine:** Start en blog/knowledge center med threat‑intel, whitepapers og case‑studier【838777347905745†L779-L789】.  Det vil etablere thought leadership og tiltrække leads.
6. **Priskalkulator & onboarding:** Implementér en selvbetjent priskalkulator og trial‑muligheder, så kunder kan estimere deres abonnement og teste platformen【838777347905745†L960-L1014】.  Onboarding bør omfatte sandbox‑miljø og klare første‑trins guider.
7. **UX og performance:** Redesign navigationen til max 5 menupunkter, fjern glitch‑effekter, forbedr kontrast og tastaturnavigation, minificér assets og aktiver lazy‑loading【838777347905745†L181-L190】.  Audit rapporten anbefaler desuden cookie‑banner, reCAPTCHA og sikkerhedshoveder【838777347905745†L183-L193】.
8. **Terminologi:** Omdøb forvirrende moduler (f.eks. PVE) til mere sigende navne og forklar værdien med klare forretningsord【838777347905745†L942-L949】.

## 5. Målgrupper og segmentering

Rapporten omfatter en detaljeret segmentanalyse for offentlig og privat sektor【333464687094006†L284-L320】:

### 5.1 Offentlig sektor

* **Høje compliance‑krav:** Offentlige institutioner håndterer borgerdata og kritisk infrastruktur.  Platformen skal støtte NIS2/ISO27001‑krav og dokumentere logning og træning【333464687094006†L284-L324】.
* **Helhedsorienteret sikkerhed:** Enterprise‑pakken anbefales, idet kombinationen af PVE, Blackbox EYE, ID‑Matrix og AUT skaber forsvar‑i‑dybden【333464687094006†L284-L324】.
* **Datasuverænitet:** Tilbyd mulighed for lokal hosting eller aftaler om datalagring i EU‐jurisdiktioner【333464687094006†L320-L322】.

### 5.2 Private virksomheder

#### SMV’er

* **Begrænsede ressourcer:** Har sjældent eget SOC; får størst værdi af Standard‑ eller Premium‑pakken som plug‑and‑play‑løsninger【333464687094006†L338-L350】.
* **Omkostningseffektivitet:** Prispunkterne er lavere end at hyre eksterne konsulenter; platformen leverer enterprise‑grade AI til en brøkdel af prisen【333464687094006†L343-L346】.
* **Skalerbarhed:** Kunden kan starte på Standard og senere opgradere til Premium eller Enterprise uden at skifte leverandør【333464687094006†L353-L355】.

#### Store virksomheder (enterprise)

* **Avancerede trusler:** Har brug for kontinuerlig pentest og avanceret forsvar mod APT’er【333464687094006†L369-L374】.
* **Integration:** Operational Bridge muliggør integration med eksisterende sikkerhedsstack og gør platformen til en *force‑multiplier* i stedet for en konkurrent【333464687094006†L379-L384】.
* **Specialistassistance:** Enterprise‑kunder får adgang til specialteams on‑demand, hvilket sparer dem for at opretholde store interne red/blue teams【333464687094006†L388-L394】.

## 6. Teknisk infrastruktur og UX‑optimering

Audit‑rapporterne leverer en klar **90‑dages roadmap** for at forbedre den eksisterende platform【838777347905745†L960-L1014】.  De vigtigste punkter, der skal implementeres, er:

1. **Fase 1 (uger 1–3):** Fjern bot‑blokering (robots.txt), implementer cookie‑banner og privacy‑politikker, redesign hero‑sektionen uden glitch, optimer performance via Gzip/Brotli og lazy‑loading【838777347905745†L998-L1002】.
2. **Fase 2 (uger 4–8):** Start rebranding‑proces, redesign navigation/IA, implementér booking‑kalender, publicer første case‑studies og whitepapers, tilføj team‑profil side, implementér security headers【838777347905745†L1003-L1008】.
3. **Fase 3 (uger 9–13):** Launch det rebrandede site, udvid blog/knowledge center, implementér priskalkulator og sandbox‑trial, forbedr tilgængelighed med ARIA‑labels, fortsæt SEO‑optimering og tilføj partner‑badges【838777347905745†L1009-L1014】.

Teknisk skal systemet også understøtte hybrid edge‑cloud‑arkitektur, offline‑first database og modulær AI, som allerede anvendes i ID‑Matrix og AIG‑projekterne【312683861554542†L11-L20】【351110377761952†L8-L14】.  En CI/CD‑pipeline med automatiserede tests, audit‑logs og compliance‑checks sikrer, at løsningen er stabil og audit‑klar fra dag ét【312683861554542†L45-L52】【351110377761952†L10-L14】.

## 7. Strategisk roadmap og fremtidige moduler

For at opbygge en platform på niveau med Darktrace og SentinelOne kræves en strategisk produktudviklingsplan:

1. **AI‑orkestrerede playbooks:** Udvikl en **Playbook Builder**, hvor kunder kan definere egne responsplaner (f.eks. isoler, sweep, patch).  Integrér med Live Command Center.
2. **Threat‑Intel API:** Byg et modul, der indsamler og korrelerer eksterne trussel-feeds (OSINT, dark‑web, nation‑state APT‑rapporter) og gør dem tilgængelige for Blackbox EYE‑assistenten.
3. **Compliance‑dashboard:** Visualiser overholdelse af GDPR/NIS2 med real‑time rapporter fra PVE, ID‑Matrix og AUT.  Rapporter kan eksporteres som PDF til revision.
4. **Partner‑økosystem:** Opbyg integrationer med endpoint‑beskyttelse (CrowdStrike, Microsoft Defender), ticketing‑systemer (Jira, Zendesk) og cloud‑platforme (AWS GuardDuty).  Operational Bridge bliver nøglen til dette.
5. **AI‑drevet risk scoring:** Udvid PVE med et risk‑score, der kvantificerer forretningsimpact for fundne sårbarheder.  Kombinér med MITRE ATT&CK‑mapping og prioritering.
6. **Mobile‑appen:** Design en mobil-app til Security Operations Managers, hvor de kan få push‑notifikationer, se Blackbox EYE‑alarmer og godkende playbooks on the go.
7. **Multi‑tenant‑support:** Implementér isoleret multi‑tenant‑arkitektur, så MSP’er kan bruge platformen til flere kunder (hvidlabel).  Dette åbner nye markeder.
8. **GenAI‑assistent:** Integrér LLM‑baseret assistent, der kan forklare alarmer, anbefale afhjælpninger og generere compliance‑dokumentation.  Med prompt‑suiten fra `blackbox_prompts_v1` er der allerede et fundament til automatiseret systemarkitektur【396741449439354†L17-L30】.

## 8. Konklusion

Blackbox EYE / Blackbox EYE går fra et mystisk niche‑brand til en **transparent, enterprise‑klar AI‑platform**.  Rapporterne viser, at kombinationen af AI og menneskelig ekspertise er stærk, men at eksekveringen hidtil har manglet klar prisstruktur, trust signals, SEO og brugervenlighed【838777347905745†L20-L32】【838777347905745†L168-L183】.  Med den nye prisramme, de forbedrede pakker og en skarpt defineret roadmap kan vi nu levere en **modulær, skalerbar og compliance‑klar sikkerhedsløsning**, der tilgodeser både SMV’er og offentlige giganter.  

Næste skridt er at få ledelsens godkendelse af rebranding‑strategien, igangsætte udviklingen af MVP‑modulerne, implementere de foreslåede website‑forbedringer og begynde at producere thought‑leadership‑indhold.  Når dette er gjort, kan Blackbox EYE positionere sig som Europas førende **digital supersoldat** inden for AI‑drevet cybersikkerhed.
