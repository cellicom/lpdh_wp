# LPDH WordPress Theme

A feature-rich, high-performance WordPress Child Theme built on the **Bootscore** framework, specifically engineered for managing the **LPDH (Legendary Pauper Commander)** community and tournament ecosystem.

## 🚀 Overview

This theme transforms WordPress into a comprehensive tournament management platform. It combines static content management with dynamic systems for player rankings, deck database management, and interactive community features.

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

### 4. Modular Page Building
- **ACF Flexible Content:** A "No Sidebar" template that allows administrators to build complex layouts (FAQ accordions, Link grids, Team lists, Action buttons) using modular blocks without touching code.

### 5. Advanced Banned List
- **Ban List:** Dynamic management of the format's restricted cards.
- **Shortcode Generator:** Custom admin tool to easily insert interactive banned card boxes into posts and articles.

### 6. Frontend Profile Governance
- **Profile Management:** Player-centric frontend dashboard to update personal details, avatars, meta preferences and decks.

## ✨ Easter Eggs & Community Interactions
The theme includes several interactive "secrets" to engage the community. Found it all!

## 📦 Installation & Development

This is a **Bootscore Child Theme**. 

### Requirements
- **WordPress 6.4+**
- **Bootscore Parent Theme** (v6.x)
- **Advanced Custom Fields PRO (v6.x)** - Mandatory for data management and modular boxes.

```bash
# Compile SCSS to CSS
npx sass assets/scss/main.scss assets/css/main.css
```

---

*Developed for the LPDH Community. Based on the Bootscore Child Theme template.*
