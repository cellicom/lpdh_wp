# LPDH WordPress Theme

A feature-rich, high-performance WordPress Child Theme built on the **Bootscore** framework, specifically engineered for managing the **LPDH (Legendary Pauper Commander)** community and tournament ecosystem.

## 🚀 Overview

This theme transforms WordPress into a comprehensive tournament management platform. It combines static content management with dynamic systems for player rankings, deck database management, and interactive community features.

---

> [!NOTE]
> This theme is the result of an experiment.
> Aside from graphical assets, the entire template was developed by AI—primarily Gemini, Copilot, and Blackbox—under my specific instructions. These prompts were sometimes detailed, and other times intentionally vague or imprecise, in order to challenge the AI's reasoning capabilities and test its understanding of the project context and objectives. This repository is public to serve as documentation of this experiment.

---

## 🛠 Technical Foundation

- **Base Framework:** [Bootscore](https://bootscore.me/) (Bootstrap 5 integrated with WordPress)
- **Engine:** Custom JavaScript (jQuery), PHP 8.1+, and SCSS.
- **Data Architecture:** Extensive use of **Advanced Custom Fields (ACF Pro)** and **Custom Post Types (CPT)** for structured data management.

## 💎 Core Features

### 1. Tournament & Event Management
- **Events:** Manage local and online tournaments with specific venue mapping and date tracking.
- **Place Management:** CPT dedicated to physical and digital venues, linking events to specific locations for historical and geographical tracking.
- **OCR Ranking Generator:** Integrated *Tesseract.js* tool to extract ranking data directly from tournament screenshots.
- **Sync Player Tool:** Automatic matching of textual names from software exports to registered WordPress user profiles.

### 2. Player Statistics & ELO System
- **Dynamic ELO Calculation:** A customized formula that updates player rankings based on match outcomes (W-L-D), opponent strength, and final tournament position.
- **Leaderboards:** Automated annual ranking generation based on competitive performance data.

### 3. Deck Management System
- **Decks:** Players can create, edit, and share their decklists.
- **Frontend Deck Editor:** Custom-built interface allowing users to create and edit decks directly from the site's frontend without dashboard access.
- **Scryfall Integration:** Real-time fetching of card art for commanders and partners via the Scryfall API.
- **Interactive Deck Visuals:** Custom image wrappers with localized card interactions.

### 4. Achievements & Milestones ⭐
- **Dynamic Achievements:** A system that tracks player statistics (Wins, Events, Elo) and automatically unlocks badges.
- **Yearly Badge System:** Achievements can be tied to specific years, featuring a ribbon overlay.
- **Admin Management:** Dedicated dashboard to manually grant/revoke badges or verify conditions.
- **Secret Achievements:** Titles and descriptions are masked on other users' profiles to maintain the mystery.

### 5. Commander Roulette
- **Randomizer:** An interactive tool that suggests a random **Uncommon Legendary Creature** for a player's next game, automatically excluding cards in the **Banned List**.
- **Rate Limiting:** Every user has **3 tokens per day**.
- **Admin Mode:** Site administrators bypass the token limit for testing or demonstration.

### 6. Modular Page Building
- **ACF Flexible Content:** A "No Sidebar" template that allows administrators to build complex layouts (FAQ accordions, Link grids, Team lists, Action buttons) using modular blocks.

### 7. Advanced Banned List & Integration
- **Scryfall Autocomplete:** Admin tool that suggests official card names and automatically fetches metadata.
- **Image Fallback:** Automatic card scan fetching from Scryfall if no image is uploaded.
- **Shortcode Generator:** Integration tool to easily insert interactive banned card boxes into posts.

### 8. Frontend Governance & Sync
- **Profile Privacy:** Player-centric dashboard to manage visibility and account settings.
- **Improved Name Matching:** Robust event synchronization algorithm that handles multi-word surnames and abbreviations.

## ✨ Easter Eggs & Community Interactions
The theme includes several interactive "secrets" to engage the community. Found it all!

## 📦 Installation & Development

This is a **Bootscore Child Theme**. 

### Requirements
- **WordPress 6.4+**
- **Bootscore Parent Theme** (v6.x)
- **Advanced Custom Fields PRO (v6.x)** - [Download](https://www.advancedcustomfields.com/pro/) - Mandatory for data management and modular boxes.
- **bs Cookie Settings (v.5.6.x)** - [Download](https://bootscore.me/documentation/bs-cookie-settings/) - Mandatory for GDPR/CCPA compliance and user consents.
- **ACF Font Awesome (v4.x)** - [Download](https://wordpress.org/plugins/advanced-custom-fields-font-awesome/) - Mandatory for selecting achievement icons.

```bash
# Compile SCSS to CSS
npx sass assets/scss/main.scss assets/css/main.css
```

> [!IMPORTANT]
> LPDH - Legendary Pauper Commander is unofficial Fan Content permitted under the Fan Content Policy. Not approved/endorsed by Wizards. Portions of the materials used are property of Wizards of the Coast. ©Wizards of the Coast LLC.
