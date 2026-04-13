# CS 432/532 – Web Science  
## Spring 2026   

---

## 👨‍💻 Author  
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia  

🔗 GitHub Repository: https://github.com/skyelogic  

---

## 📋 Table of Contents

- [Homework 1](#homework-1)
- [Homework 2](#homework-2)
- [Homework 3](#homework-3)
- [Homework 4](#homework-4)
- [Homework 5](#homework-5)
- [Homework 6](#homework-6)
- [License](#-license)
- [Acknowledgments](#-acknowledgments)

---

## Homework 1  
🔗 https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HM1  

### cURL URI Collector (+500 URIs) & Graph Visualization  

Homework 1 focuses on large-scale URI collection and structural web analysis.  

This project includes:

- Automated URI collection using `cURL`
- Aggregation of 500+ unique URIs
- Graph-based visualization of link structures
- Interactive exploration of network relationships
- Structural analysis of connectivity patterns

The goal was to understand how web resources interconnect and to visualize those relationships through graph representations.

---

## Homework 2  
🔗 https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW2  

### QuillCheck – AI Authenticity Analyzer  

QuillCheck is a practical web science application designed to evaluate the authenticity of web content.

### How It Works

1. Accepts any publicly accessible URL  
2. Fetches full HTML content  
3. Strips boilerplate and extracts meaningful text  
4. Processes content through a two-stage pipeline  
5. Sends refined text to an AI detection API  
6. Returns an authenticity probability score  

This assignment demonstrates:

- Web data extraction  
- Content parsing and cleaning  
- API integration  
- AI-assisted classification  
- Applied web science principles  

QuillCheck moves beyond static analysis and explores real-world applications of web data processing and AI evaluation.

---

## Homework 3  
🔗 [https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW3](https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW3)

### Web Archiving - TimeMaps & Memento Analysis (Part 1)

This assignment demonstrates:
- Collecting TimeMaps for 520 unique URIs using the MemGator Memento Aggregator
- Querying multiple web archives simultaneously to measure how well the web is preserved
- Automating large-scale data collection with bash scripting and rate limiting
- Parsing and analyzing JSON TimeMap data with Python
- Applying the Memento Protocol (RFC 7089) to real-world web science research

This project explores a fundamental question in web science:
**how much of the web is actually archived, and how well?**

---

## Homework 4  
🔗 [https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW4](https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW4)

### Web Archiving - Memento Age Analysis & WARC Collection (Part 2)  

This assignment demonstrates:
- Parsing JSON TimeMaps to extract earliest memento datetimes for 441 URI-Rs
- Visualizing the relationship between URI-R age and memento count using Python (matplotlib)
- Archiving 10 live webpages using the ArchiveWeb.page browser extension (Webrecorder)
- Replaying WACZ archives locally with ReplayWeb.page and analyzing captured URLs by file type

This project explores two core web archiving concepts:  
How age and popularity shape a URI-R's memento history, and how browser-based archiving captures the full resource graph of modern web pages.

---

## Homework 5  
🔗 [https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW5](https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW5)

### Graph Partitioning - Zachary's Karate Club  

This assignment demonstrates:  
- Loading and visualizing real-world social network data using NetworkX  
- Implementing the Girvan-Newman graph partitioning algorithm from scratch  
- Computing edge betweenness centrality using BFS-based back-propagation (Brandes, 2001)  
- Iteratively removing high-betweenness edges and tracking connected components  
- Comparing a mathematically-predicted community split to the observed real-world outcome  

This project explores a foundational question in social network analysis:  
Can the structure of social interactions alone predict how a group will fracture?  

---

## Homework 6
🔗 [https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW6](https://github.com/odu-cs432-websci/spr26-skyelogic/tree/main/HW6)

### Recommendation Systems

This assignment applies collaborative filtering techniques from Programming Collective Intelligence (Chapter 2) to the MovieLens 100K dataset. The goal is to:  
- Identify demographically similar users to act as a "substitute me"  
- Find users whose movie ratings correlate most (and least) with the substitute  
- Generate personalized movie recommendations for the substitute  
- Explore item-based similarity using my own favorite and least favorite films  

---

## 📝 License

This project is submitted as coursework for CS 432/532 at Old Dominion University.

---

## 🙏 Acknowledgments

Special thanks to:
- **NASREEN MUHAMMAD ARIF** - Course instructor
- **Old Dominion University** - Web Science program

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
