# HW1 - Web Science Intro
**CS 432/532 - Web Science**  
**Spring 2026**

---

## 👨‍💻 Author
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia

📅 **Due Date:** February 15, 2026  
🔗 **GitHub Repository:** [spr26-skyelogic](https://github.com/odu-cs432-websci/spr26-skyelogic)

---

## 📋 Table of Contents
- [Question 1: Directed Graph Visualization](#-question-1-directed-graph-visualization)
- [Question 2: Using cURL](#-question-2-using-curl)
- [Question 3: Web URI Collector](#%EF%B8%8F-question-3-web-uri-collector)
- [Files in This Repository](#-files-in-this-repository)
- [Technologies Used](#technologies-used)
- [How to Run](#-how-to-run)
- [References](#-references)

---

## 📊 Question 1: Directed Graph Visualization

### Task
Draw a directed graph showing node connections and classify nodes into SCC, IN, OUT, Tendrils, Tubes, and Disconnected components.

### Solution
Created an **interactive HTML visualization** with draggable nodes and real-time force-directed layout.

<p align="center">
  <img src="https://github.com/user-attachments/assets/eab6a4ab-c2e0-408b-a894-2a998dc8e1f5" alt="Graph Visualization" width="800"/>
</p>

🔗 **[View Live Demo](https://donnelgarner.com/projects/CS432/HW1/directed_graph.html)**

### Technologies Used
- **HTML5 Canvas** - Drawing surface for graph rendering
- **CSS3** - Styling and layout
- **Vanilla JavaScript** - Interactive physics simulation
  - Drag-and-drop functionality
  - Real-time animation using `requestAnimationFrame()`

### Graph Analysis Results

| Category | Nodes | Description |
|----------|-------|-------------|
| **SCC** (Giant) | `A, B, C, D, G` | Strongly connected component where every node can reach every other node |
| **IN** | `E, F, M` | Can reach SCC but cannot be reached from SCC |
| **OUT** | `H, L` | Can be reached from SCC but cannot reach back |
| **Tendrils** | `None` | Would be nodes reachable from IN or SCC but not both |
| **Tubes** | `J, N, O` | Create pathway from IN → OUT bypassing SCC<br>Path: `E → O → J → N → L` |
| **Disconnected** | `I, K` | Isolated component with no connection to main structure |

### Files
- `directed_graph.html` - Interactive visualization
- `directed_graph.png` - Static image

---

## 🌐 Question 2: Using cURL

### Task
Demonstrate proficiency with cURL by making HTTP requests with various options to analyze User-Agent headers.

### Part A: Browser User-Agent
<p align="center">
  <img src="https://github.com/user-attachments/assets/09990604-c1ab-4d99-bb0e-1f3f07e67906" alt="Browser User-Agent" width="700"/>
</p>

**Result:** Shows the default User-Agent string sent by the web browser.

---

### Part B: cURL with Headers Visible

```bash
curl -L -i -A "CS432/532" https://www.cs.odu.edu/~mweigle/courses/cs532/ua_echo.php
```

<p align="center">
  <img src="https://github.com/user-attachments/assets/73172f4d-003c-4d97-90b1-7306fae6ab91" alt="cURL Headers" width="700"/>
</p>

**Options Explained:**
- `-L` : Follow HTTP redirects automatically
- `-i` : Include HTTP response headers in output
- `-A "CS432/532"` : Set custom User-Agent header

**Result:** Displays both HTTP headers and HTML content with custom User-Agent.

---

### Part C: Save Output to File

```bash
curl -L -A "CS432/532" -o output.html https://www.cs.odu.edu/~mweigle/courses/cs532/ua_echo.php
```

<p align="center">
  <img src="https://github.com/user-attachments/assets/6d35fb7f-cf19-4a74-bc2a-8732dfc4cda5" alt="cURL Save File" width="700"/>
</p>

**Options Explained:**
- `-o output.html` : Save response to file named "output.html"
- Verified file creation with `ls` command

---

### Part D: View HTML in Browser

🔗 **[View HTML Output](https://donnelgarner.com/projects/CS432/HW1/output.html)**

<p align="center">
  <img src="https://github.com/user-attachments/assets/77b7004c-6b1c-4812-af00-be4030f37d23" alt="HTML Output" width="700"/>
</p>

**Result:** Browser correctly displays the custom User-Agent "CS432/532" as set by cURL.

---

## 🕷️ Question 3: Web URI Collector

### Task
Create a Python program to collect 500+ unique URIs of HTML pages with >1000 bytes using web crawling techniques.

### Methodology

**Hybrid Multi-Seed Approach:**
1. **Multiple Seed URLs** - Started with many diverse websites across different domains
2. **Random Exploration** - For each seed, randomly selected from collected URIs to explore further
3. **Link Limiting** - Max links per page to prevent hanging
4. **Automatic Failover** - If one seed fails, moves to next seed
5. **Deduplication** - Python sets ensure all 500 URIs are unique

### Seed URLs Used

<p align="center">
  <img src="https://github.com/user-attachments/assets/38787416-82cc-4306-bf30-965779cccf59" alt="Seed URLs Screenshot" width="800"/>
</p>

**Primary Seeds (from a few of my own projects):**
- 🎮 [OurGemCodes](http://www.ourgemcodes.com) - Personal gaming guide site (3,500+ posts)
- 👨‍💼 [DonnelGarner.com](http://www.donnelgarner.com) - Personal portfolio
- 👁️ [ViaPist.com](http://www.viapist.com) - Visual impairment application
- 🔧 [HostRepair.com](http://www.hostrepair.com) - Hosting repair site

**Additional Seeds:**
- YouTube, Medium, IMDb, Apple App Store, Google Play
- X (Twitter), Facebook, Reddit, LinkedIn
- Wikipedia, Britannica, Gizmodo, PCMag

### Key Features

✅ **Timeout Protection** - 3-second timeout prevents hanging  
✅ **Redirect Handling** - Follows HTTP redirects to final URL  
✅ **Content Validation** - Checks `Content-Type: text/html` and `Content-Length > 1000`  
✅ **Polite Crawling** - 50ms delay between requests  
✅ **Verbose Logging** - Detailed progress output for debugging  
✅ **SSL Skip** - Bypasses SSL verification to prevent hangs

### Challenges Encountered

**Problem:** Initial implementation would hang indefinitely on random seeds.

### Results

📊 **Successfully collected 500+ unique URIs**  
⏱️ **Runtime:** Approximately 15-20 minutes  
💾 **Output File:** `uris.txt`

### Files
- `collect.py` - Main collection script
- `uris.txt` - Final list of 500+ unique URIs

---

## 📁 Files in This Repository

```
spr26-skyelogic/
├── README.md                           # This file
│
├── Question 1 - Graph Visualization/
│   ├── directed_graph.html             # Interactive visualization
│   └── directed_graph.png              # Static graph image
│
├── Question 2 - cURL/
│   ├── output.html                     # cURL output file
│   ├── curl_assignment_guide.txt       # cURL reference guide
│   └── screenshots/                    # All screenshots
│
└── Question 3 - URI Collector/
    ├── collect.py                      # Main script
    └── uris.txt                        # Collected URIs (500+)
```

---

## 🛠️ Technologies Used

### Question 1
- HTML5 Canvas API
- CSS3 (Flexbox, shadows, transitions)
- Vanilla JavaScript (ES6+)
- Mouse event handling

### Question 2
- cURL command-line tool
- SSH/Terminal (Putty / VSCODE)
- HTTP protocol understanding
- Bandicam (screenshots)

### Question 3
- **Python 3.x**
- **Libraries:**
  - `requests` - HTTP requests
  - `beautifulsoup4` - HTML parsing
  - `urllib3` - URL utilities
- **Data Structures:**
  - Sets (deduplication)
  - Lists (link storage)
- **Algorithms:**
  - Random exploration
  - Breadth-first search (MEH!)

---

## 🚀 How to Run

### Question 1: Graph Visualization
```bash
# Just open in a web browser
open directed_graph.html
# Or visit: https://donnelgarner.com/projects/CS432/HW1/directed_graph.html
```

### Question 2: cURL Commands
```bash
# Part B - Headers visible
curl -L -i -A "CS432/532" https://www.cs.odu.edu/~mweigle/courses/cs532/ua_echo.php

# Part C - Save to file
curl -L -A "CS432/532" -o output.html https://www.cs.odu.edu/~mweigle/courses/cs532/ua_echo.php
```

### Question 3: URI Collector
```bash
# Install dependencies
pip install requests beautifulsoup4

# Run the collector (collects 500 URIs)
python collect.py 500 > uris.txt

# For testing (collect only 50)
python collect.py 50 > test.txt
```

**Windows Users:**
```powershell
py -m pip install requests beautifulsoup4
py collect.py 500 > uris.txt
```

---

## 📚 References

1. **Course Materials**
   - [HW1 Assignment](https://github.com/odu-cs432-websci/public-spr26/blob/main/HW1-intro.md)
   - Module-01 Web Science Architecture slides

2. **Development Tools**
   - [Brave Browser AI Search](https://brave.com/ai/)
   - [w3schools Python Tutorial](https://www.w3schools.com/python/)
   - [Claude Code Documentation](https://code.claude.com/docs/en/vs-code)
   - [BeautifulSoup Documentation](https://www.crummy.com/software/BeautifulSoup/bs4/doc/)
   - [Requests Documentation](https://requests.readthedocs.io/)

3. **Graph Theory Resources**
   - Broder et al. "Graph structure in the Web" (2000)

4. **Development Assistance**
   - Claude AI (Anthropic) - Code development, debugging, documentation
   - ChatGPT - Additional research and problem-solving

---

## 🎓 Learning Outcomes

Through this assignment, I gained hands-on experience with:

✅ **Web Graph Theory** - Understanding SCC, bow-tie structure, and component classification  
✅ **HTTP Protocol** - Working with headers, redirects, and content negotiation  
✅ **Command-Line Tools** - Proficiency with cURL and various options  
✅ **Web Scraping** - Ethical crawling, link extraction, and data validation  
✅ **Python Programming** - HTTP requests, HTML parsing, set operations  
✅ **Data Visualization** - Interactive graphics with Canvas API and JavaScript  
✅ **Problem Solving** - Debugging timeout issues, handling edge cases  

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
