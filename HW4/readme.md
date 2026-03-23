# Homework 4 - Web Archiving, Part 2

---

## 👨‍💻 Author  
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia  

**CS432 - Web Science**  
**Spring 2026**  

📅 **Due Date:** March 22, 2026  
🔗 **GitHub Repository:**  
🌐 **Live Site:** N/A

---

## 📋 Table of Contents  
- [How to Run](#-how-to-run)
- [Q1: Analyze Datetimes of Mementos](#-q1-analyze-datetimes-of-mementos)
- [Q2: Explore ArchiveWeb.page and ReplayWeb.page](#%EF%B8%8F-q2-explore-archivewebpage-and-replaywebpage)
- [Technologies Used](#%EF%B8%8F-technologies-used)
- [References](#-references)

---

### Web Archiving: Memento Age Analysis & WARC Collection

This assignment demonstrates:
- Parsing JSON TimeMaps to extract earliest memento datetimes for 441 URI-Rs
- Calculating URI-R age relative to a collection date and visualizing results
- Using Python (matplotlib) to create a scatterplot of age vs. memento count
- Archiving live webpages using the ArchiveWeb.page browser extension (Webrecorder)
- Replaying WACZ archives locally with ReplayWeb.page and analyzing captured URLs by file type

This project explores two core web archiving concepts:
**how age and popularity shape a URI-R's memento history, and how browser-based archiving captures the full resource graph of modern web pages.**

---

## 🚀 How to Run

### Requirements
- Python 3
- `matplotlib` library (`pip install matplotlib`)
- TimeMaps from HW3

### Step 1: Parse TimeMaps and generate scatterplot
```bash
python3 analyze_datetimes.py
```
> This reads all JSON TimeMaps, extracts the earliest memento datetime for each URI-R,
> calculates age in days relative to **March 15, 2026**, and outputs the scatterplot.

### Step 2: View the output
The scatterplot is saved as `HW4_Q1_scatterplot.png` in the working directory.

---

## 📊 Q1: Analyze Datetimes of Mementos

### Process
Using the 500 TimeMaps saved during HW3, I parsed each JSON file to extract the datetime of the **first (earliest) memento** for every URI-R with more than 0 mementos.

- **Collection date:** March 15, 2026
- **URI-Rs with mementos:** 441
- **URI-Rs with 0 mementos:** 59 (empty files - consistent with HW3 Q2 results)
- **Age** was calculated as the number of days between the collection date and the earliest memento datetime

The scatterplot uses a **logarithmic y-axis** to handle the wide spread in memento counts (1 to 35,322).

### Scatterplot

![HW4 Q1 Scatterplot — URI-R Age vs. Memento Count](https://github.com/odu-cs432-websci/spr26-skyelogic/blob/main/HW4/HW4_Q1_scatterplot.png)

*Figure 1. Age of URI-R (days since earliest memento) vs. Number of Mementos (log scale). n = 441 URI-Rs. Collection date: March 15, 2026.*

---

### Q: What can you say about the relationship between the age of a URI-R and the number of its mementos?

> There is a **general positive trend**, older URI-Rs tend to have more mementos, which makes intuitive sense since they have had more time to be crawled. However, the relationship is noisy. Some relatively young URI-Rs have very high memento counts (e.g., `https://developers.google.com/youtube` with 35,322 mementos at ~14 years old), because high-traffic, frequently updated pages are crawled far more aggressively. Factors like **page popularity and update frequency** can matter as much as raw age.

---

### Q: What URI-R had the oldest memento? Did that surprise you?

> **`https://www.odu.edu/`** had the oldest first memento, dating back to **December 21, 1996**, making it **10,675 days (~29.2 years)** old at the time of collection, with **12,851 mementos**.
>
> This was not surprising. TIL that ODU is also home to one of the leading web archiving research groups (WS-DL), so their domain being among the earliest archived pages is very fitting.

---

### Q: How many URI-Rs had an age of < 1 week?

> **0 URI-Rs** had a first memento captured in the same week as data collection. The youngest first memento in the dataset was **22 days old**. This means all URI-Rs in the dataset were already known to web archives prior to the collection date.

---

## 🗂️ Q2: Explore ArchiveWeb.page and ReplayWeb.page

### WACZ Archive File
The archived collection is publicly available here:

🔗 **[homework-4.wacz (Google Drive)](https://drive.google.com/file/d/1Jw_hXnrESKq9jr2XoBbtD-Q9qIbOuP5a/view?usp=sharing)**

---

### Q: Why did you choose this particular topic?

> I chose **SEO and web business-related topics** because they are personally meaningful to me. Before joining the military, I owned a web development company and created online radio stations for gaming companies and social communities. Tools covering keyword research, domain registration, community software, and text-to-speech were directly relevant to the work I used to do. Archiving them felt like preserving a snapshot of an industry I know well.

---

### Q: Did you have any issues in archiving the webpages?

> No issues were encountered. The ArchiveWeb.page extension worked smoothly for all 10 pages. One observation: some pages had notably large file sizes (e.g., Animal Jam at 9.49 MB) due to the volume of images, scripts, and media that modern pages load, but all archived without errors.

---

### Q: Do the archived webpages look like the original webpages?

> Yes, the archived pages looked nearly identical to the originals. I initially expected JavaScript-driven interactions might not replay correctly, but ArchiveWeb.page captures the full browser session including JS execution, so the replayed pages appeared and behaved like the live originals.

---

### ReplayWeb.page: Pages Tab

The screenshot below shows all 10 archived pages loaded from `homework-4.wacz` in ReplayWeb.page. The browser address bar confirms the WACZ file is being loaded from the local computer (`replayweb.page/?source=file%3A%2F%2F`...).

![ReplayWeb.page Pages Tab Screenshot](https://github.com/odu-cs432-websci/spr26-skyelogic/blob/main/HW4/bandicam%202026-03-22%2020-40-07-730.png)

*Figure 2. ReplayWeb.page Pages tab : 10 archived pages loaded from homework-4.wacz on local disk.*

---

### Q: How many URLs were archived in the WARC file? How does this compare to the number of Pages?

> The WACZ file contained **715 total URLs**, compared to only **10 Pages** — a ratio of roughly **71.5 URLs per page**.
>
> The large difference is because ArchiveWeb.page captures every resource the browser requests to render each page: images, stylesheets, JavaScript files, fonts, JSON API responses, and more. The **Pages** tab represents the top-level documents visited, while the **URLs** tab shows the full resource dependency graph needed to faithfully reconstruct those pages.

---

### Bar Chart: URLs by File Type

![HW4 Q2 Bar Chart — URLs by File Type](https://github.com/odu-cs432-websci/spr26-skyelogic/blob/main/HW4/HW4_Q2_barchart.png)

*Figure 3. Number of archived URLs in homework-4.wacz by file type (715 total URLs across 10 pages).*

| File Type | URL Count |
|-----------|-----------|
| Images | 179 |
| JavaScript (.js) | 141 |
| CSS | 80 |
| Fonts | 38 |
| HTML | 21 |
| JSON | 16 |
| Plain Text | 3 |

---

### Q: Which file type had the most URLs? Were you surprised by this?

> **Images** had the most URLs at **179**, edging out JavaScript at **141**. This was not surprising — modern websites are highly visual, relying on hero images, icons, thumbnails, logos, and backgrounds, so images naturally dominate the resource list. JavaScript came in second, reflecting how JS-heavy the modern web is (analytics, UI frameworks, ad scripts, etc.). Together, images + JS + CSS account for **400 of the 715 URLs (56%)**, illustrating just how resource-intensive modern web pages are compared to the simple HTML documents of the early web.

---

## ⚠️ Challenges Encountered

- **Wide memento count range** — Counts ranged from 1 to 35,322, requiring a logarithmic y-axis on the scatterplot to display data meaningfully without the outliers dominating the chart.
- **59 empty TimeMaps** — Files saved with 0 bytes from HW3 (representing URI-Rs with no mementos) had to be handled gracefully in the parsing script to avoid JSON decode errors.
- **Large WACZ file sizes** — Some pages (e.g., Animal Jam at 9.49 MB) generated large captures due to the volume of embedded media and scripts on modern web pages.

---

## 🛠️ Technologies Used

### Languages & Runtimes
- **Python 3** — parse TimeMaps, calculate ages, and generate the scatterplot
- **JavaScript (Node.js)** — generate the HW4-report.docx via the `docx` library

### Tools & Libraries
- **Python `matplotlib`** — scatterplot (Q1) and bar chart (Q2)
- **Python `json`** — parse TimeMap JSON files
- **Python `datetime`** — calculate age in days between collection date and earliest memento
- **ArchiveWeb.page** — browser extension for recording live webpages into WACZ format
- **ReplayWeb.page** — local WACZ replay tool for reviewing archived content
- **WSL** (Windows Subsystem for Linux) — run Python scripts on Windows

---

## 📚 References

✅ **[ArchiveWeb.page](https://archiveweb.page)** — Browser extension for web archiving (Webrecorder Project)  
✅ **[ReplayWeb.page](https://replayweb.page)** — Local WACZ replay tool (Webrecorder Project)  
✅ **[Webrecorder Tools](https://webrecorder.net/tools)** — Suite of open-source archiving tools  
✅ **[Internet Archive Wayback Machine](https://web.archive.org)** — Primary memento source  
✅ **[Memento Protocol (RFC 7089)](https://www.rfc-editor.org/rfc/rfc7089)** — How web archiving works  
✅ **[MemGator GitHub](https://github.com/oduwsdl/MemGator)** — Memento aggregator used in HW3  
✅ **[Claude](https://claude.ai)** — Development assistance  

---

## 📝 License

This project is submitted as coursework for CS 432 at Old Dominion University.  
The complete commercial version is protected. You may use this at your own discretion.

---

## 🙏 Acknowledgments

Special thanks to:  
- **NASREEN MUHAMMAD ARIF** — Course instructor  
- **Old Dominion University** — Computer Science program  

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
