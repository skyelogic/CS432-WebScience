# Homework 3 - Web Archiving, part 1

---

## 👨‍💻 Author  
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia  

**CS432 – Web Science**  
**Spring 2026**  

📅 **Due Date:** March 15, 2026  
🔗 **GitHub Repository:** 
🌐 **Live Site:** N/A

---

## 📋 Table of Contents  
- [How to Run](#-how-to-run)
- [Q1: Get TimeMaps for each URI](#-q1-timemaps)
- [Q2: Analyze Mementos](#-q2-mementos)
- [Technologies Used](#%EF%B8%8F-technologies-used)
- [References](#-references)

---

## 🚀 How to Run

### Option 1: Visit Website

### Option 2: PHP Built-in Server

### Option 3: Deploy to Web Server

---

## 📊 Q1: TimeMaps

---

## 📐 Q2: Mementos

---

## ⚠️ Challenges Encountered
Problem: Some of the largest challenges, aside from VPS issues like caching, DNS, etc...

- Boilerplate Stripping was the biggest technical headache. The first version of strip_boilerplate() was essentially useless. It only removed <script> and <style> tags but left headers, footers, nav bars, sidebars, and all the structural chrome intact. The cached files were full of noise. It took a complete rewrite with a multi-pass heuristic approach, pretty much nuking entire tag families with their content, regex-matching on id/class keywords, running the noise removal twice to catch nested containers, and extracting only the <body> to get clean text worth sending to EdenAI.
- The input[type="url"] vs input[type="text"] CSS mismatch. When the TF-IDF page was built, the scan form CSS only targeted input[type="url"] specifically, so the text input on tfidf-table.php never got flex: 1. Had to patch it in four places across the stylesheet.
- Windows line endings (CRLF) in the PHP files. When trying to do string replacements on config.php, the \r characters embedded in every line caused the replacement strings to not match, even when the content looked identical. Required a sed strip pass before edits could land.
- IDF requires the full corpus before you can compute anything. This is a conceptual trap that would have produced subtly wrong results. If you compute IDF document-by-document as you load each file, df(t) is wrong for every document except the last one.
- Dark mode defaulting to the OS preference instead of light. The initial JS checked prefers-color-scheme: dark and respected the system setting, which meant users on dark-mode OS setups would land on a dark site even on first visit. Since the parchment aesthetic is the whole point, that was the wrong default.

## 🛠️ Technologies Used

### Languages & Runtimes
- **PHP 8+** — backend fetch, stripping, caching, database writes, and template rendering
- **JavaScript (Vanilla)** — dark mode toggle with `localStorage` persistence
- **HTML5** — semantic structure
- **CSS3** — custom parchment design system, CSS variables, responsive grid, dark mode via `html.dark` class

### Libraries & APIs
- **[EdenAI](https://www.edenai.run/)** — multi-provider AI detection API (Sapling provider)
- **SQLite** (via PHP PDO) — lightweight scan history and URI-hash registry
- **Google Fonts** — Cinzel Decorative, Cinzel, Playfair Display, EB Garamond

### External Tools
- **cURL** (PHP) — HTTP fetching with redirect following and custom user-agent
- **MD5** (PHP `md5()`) — URL hashing for deterministic cache filenames
- **SSH / SFTP** — deployment to ODU web server
- **VS Code** — primary development environment

---

## 📚 References

✅ **[EdenAI Documentation](https://docs.edenai.run/)** - AI Detection API reference  
✅ **[PHP cURL Manual](https://www.php.net/manual/en/book.curl.php)** - HTTP fetching  
✅ **[PHP PDO / SQLite](https://www.php.net/manual/en/book.pdo.php)** - database layer  
✅ **[MDN: CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)** - CSS variable system  
✅ **[Google Fonts](https://fonts.google.com/)** - Cinzel, Playfair Display, EB Garamond  
✅ **[Claude](https://claude.ai)** - Development assistance    

---

## 📝 License

This project is submitted as coursework for CS 432 at Old Dominion University.  
The complete commercial version is protected. You may use this at your own discretion.  

---

## 🙏 Acknowledgments

Special thanks to:  
- **NASREEN MUHAMMAD ARIF** - Course instructor  
- **Old Dominion University** - Computer Science program  

---

<p align="center">
  <strong>Made with ☕ and 💻 by Donnel Garner</strong><br>
  <sub>Old Dominion University | CS 432 | Spring 2026</sub>
</p>

---

<p align="center">
  <a href="https://donnelgarner.com">🌐 Personal Website</a> •
  <a href="https://github.com/skyelogic">💻 GitHub</a>
</p>
