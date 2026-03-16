# Homework 3 - Web Archiving, part 1

---

## 👨‍💻 Author  
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia  

**CS432 - Web Science**  
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

### Requirements
- WSL or Linux terminal
- MemGator binary (see Q1)
- Python 3

### Step 1: Run MemGator to collect TimeMaps
```bash
bash run_memgator.sh
```
> ⚠️ This will take ~2 hours to complete (520 URIs × 15 second sleep)

### Step 2: Analyze the TimeMaps
```bash
python3 mementos.py
```

---

## 📊 Q1: TimeMaps

### Process
TimeMaps were collected for each of the 520 unique URIs from HW1 using a 
locally installed version of MemGator.

The following command was used for each URI:
```bash
./memgator-windows-amd64.exe -c "ODU CS432/532 dgarn008@odu.edu" \
    -a https://raw.githubusercontent.com/odu-cs432-websci/public/main/archives.json \
    -F 2 -f JSON \
    <URI> > timemaps/<md5hash>.json
```

### Notes
- A 15-second sleep was added between each request to avoid getting blocked
  by web archives
- The `-a` flag points to an alternate `archives.json` file as required
- If MemGator returned nothing for a URI, an empty file was saved —
  this means 0 mementos for that URI
- TimeMaps are stored in the `/timemaps` folder, named by the MD5 hash of
  each URI

---

## 📐 Q2: Mementos

### Memento Count Table

| Mementos  | URI-Rs |
|-----------|--------|
| 0         |        |
| 1-10      |        |
| 11-50     |        |
| 51-100    |        |
| 101-500   |        |
| 501+      |        |

> 📝 *Table will be filled in once MemGator finishes collecting all TimeMaps*

### Q: What URI-Rs had the most mementos? Did that surprise you?

> 📝 *To be filled in after analysis is complete*

---

## ⚠️ Challenges Encountered

- **Windows line endings (CRLF)** — The bash script was created on Windows,
  which caused `\r` characters to break the script in WSL. Fixed using:
  `sed -i 's/\r//' run_memgator.sh`
- **MemGator consuming stdin** — The while loop only processed the first URI
  because MemGator was consuming stdin. Fixed by adding `< /dev/null` to
  redirect MemGator's stdin away from the loop.
- **chmod not available on Windows** — Had to use WSL instead of PowerShell
  to run the bash script since Windows doesn't support Unix commands natively.

---

## 🛠️ Technologies Used

### Languages & Runtimes
- **Bash**: shell script to automate MemGator calls across all 520 URIs
- **Python 3**: parse TimeMaps, count mementos, and generate summary table

### Tools & Libraries
- **MemGator**: Memento aggregator that queries multiple web archives
- **Python `json`**: parse TimeMap JSON output from MemGator
- **Python `hashlib`**: MD5 hashing of URIs for consistent filenames
- **Python `collections.Counter`**: tally mementos into bins for the table
- **WSL** (Windows Subsystem for Linux): run bash scripts on Windows

---

## 📚 References

✅ **[MemGator GitHub](https://github.com/oduwsdl/MemGator)** - Memento aggregator tool  
✅ **[Memento Protocol (RFC 7089)](https://www.rfc-editor.org/rfc/rfc7089)** - How web archiving works  
✅ **[Wayback Machine](https://web.archive.org/)** - Manual sanity checks on URIs  
✅ **[archives.json](https://raw.githubusercontent.com/odu-cs432-websci/public/main/archives.json)** - Alternate archives list used with MemGator  
✅ **[Claude](https://claude.ai)** - Development assistance  

---

## 📝 License

This project is submitted as coursework for CS 432 at Old Dominion University.  

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
