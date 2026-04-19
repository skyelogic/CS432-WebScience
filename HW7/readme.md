# HW7 - Email Classification  

**CS432 - Web Science | Spring 2026**  
**Student:** Donnel Garner | **GitHub:** skyelogic  
**Instructor:** Nasreen Muhammad Arif | **Due:** April 19, 2026  

---

## Table of Contents

- [Overview](#overview)
- [Technologies](#technologies)
- [Q1: Dataset Creation](#q1-dataset-creation)
- [Q2: Naive Bayes Classifier](#q2-naive-bayes-classifier)
- [Q3: Confusion Matrix](#q3-confusion-matrix)
- [Files](#files)
- [References](#references)

---

## Overview

This assignment builds a **Naive Bayes email classifier** to distinguish between two types of spam:
- **On-topic (`phishing`)**: account-threat emails claiming your cloud, streaming, or bank account is locked/suspended and data will be deleted
- **Off-topic (`other`)**: general spam (fake prizes, health cures, loan offers, crypto schemes)

The classifier is implemented from scratch using the Naive Bayes approach from *Programming Collective Intelligence* Ch. 6, adapted from the class Colab notebook.

---

## Technologies

| Tool | Purpose |
|------|---------|
| Python 3 | Classifier implementation and training/testing |
| Naive Bayes | Probabilistic text classification |
| Plain-text `.txt` | Email format (HTML-free) |

---

## Q1: Dataset Creation

### Topic

> **What topic did you decide to classify on?**

The topic is **phishing / account-threat spam**.

**On-topic (phishing):** Emails that impersonate a legitimate service: cloud storage, streaming platforms (Peacock), banks, Apple, Google, Microsoft: and use fear tactics to claim the user's account is locked, their data is being deleted, or their payment has been declined. These emails demand immediate action (click a link, verify identity, update billing).

**Off-topic (other):** All other spam: promotional scam emails offering fake prizes, miracle health cures, easy money, free products, or unsolicited SEO/marketing services. These share the spam domain but have a completely different vocabulary and intent.

### Dataset Structure

<details>
<summary><strong>Training Dataset (40 emails)</strong></summary>

| Folder | Label | Count | Source |
|--------|-------|-------|--------|
| `training/spam_phishing/` | phishing | 20 | Personal spam folder + representative examples |
| `training/spam_other/` | other | 20 | Personal spam folder + representative examples |

**Phishing training emails include:**
- Cloud storage locked / photos to be deleted (real emails from personal spam folder)
- Peacock account suspended: no payment needed (real)
- Apple ID locked, Microsoft account suspended, PayPal limited
- Amazon locked, Netflix payment failed, Google storage full
- Bank of America unauthorized access, Gmail final warning
- iCloud compromised, Dropbox suspended, generic account-threat emails

**Other-spam training emails include:**
- Lowe's / Costco exclusive tool/wagon giveaway (real)
- Doctor-approved weight loss / Ozempic spam (real)
- SEO solicitation (real)
- Diabetes-reversal cure (real)
- IPTV free trial, Medicare kit, casino deposit scam (real)
- Class action settlement notice (real)
- Walmart gift card, Samsung giveaway, Amazon prize
- Work-from-home, car warranty, free solar panels

</details>

<details>
<summary><strong>Testing Dataset (10 emails)</strong></summary>

| Folder | Label | Count |
|--------|-------|-------|
| `testing/spam_phishing/` | phishing | 5 |
| `testing/spam_other/` | other | 5 |

**Phishing test emails:**
- Spotify account suspended: verify now
- OneDrive storage full: files deleted today
- Account suspended: unusual activity detected
- Final warning: Gmail deleted in 48 hours
- Chase bank account locked: immediate action required

**Other-spam test emails:**
- Pre-approved for a $10,000 personal loan
- Reverse diabetes naturally with ancient remedy
- Claim free iPhone 16 Pro: limited time offer
- Make $2,000 this weekend with crypto trading
- Free Disney World vacation package

</details>

---

## Q2: Naive Bayes Classifier

### How It Works

The classifier (`scripts/classify_emails.py`) uses the Naive Bayes approach from the class Colab notebook. It operates in three stages:

**1. Feature Extraction (`getwords`)**
Each email is converted to a dictionary of unique words. The function splits on non-letter characters, keeps tokens between 3–20 characters, lowercases everything, and deduplicates. This is the *Multiple Bernoulli* representation: we care whether a word appeared, not how many times.

**2. Training (`train`)**
Called once per email. Updates two counters:
- `fc`: how many training documents in each category contained each word
- `cc`: how many total documents were trained per category

After 40 training calls (20 per class), the classifier has a full vocabulary with per-class word counts.

**3. Weighted Probability + Classification**
For each test email, computes P(category) × ∏ P(word | category) for both labels and picks the highest. Raw word probabilities are smoothed using `weightedprob()`, which blends the observed count with an assumed probability of 0.5: preventing rare words from dominating the calculation.

### Running the Classifier

```bash
python3 scripts/classify_emails.py
```

### Classification Results

| Email File | Description | Actual | Predicted | Correct? |
|-----------|-------------|--------|-----------|----------|
| test_ph_01.txt | Spotify account suspended: verify now | phishing | phishing | ✅ Yes |
| test_ph_02.txt | OneDrive storage full: files deleted today | phishing | phishing | ✅ Yes |
| test_ph_03.txt | Account suspended: unusual activity detected | phishing | phishing | ✅ Yes |
| test_ph_04.txt | Final warning: Gmail deleted in 48 hours | phishing | phishing | ✅ Yes |
| test_ph_05.txt | Chase bank account locked: action required | phishing | phishing | ✅ Yes |
| test_ot_01.txt | Pre-approved for a $10,000 personal loan | other | other | ✅ Yes |
| test_ot_02.txt | Reverse diabetes naturally with ancient remedy | other | other | ✅ Yes |
| test_ot_03.txt | Claim free iPhone 16 Pro: limited time offer | other | other | ✅ Yes |
| test_ot_04.txt | Make $2,000 this weekend with crypto trading | other | other | ✅ Yes |
| test_ot_05.txt | Free Disney World vacation package | other | other | ✅ Yes |

**Accuracy: 10/10 = 100%**

> **Q: For those emails the classifier got wrong, what factors might have caused the error?**

No emails were misclassified in this run. However, likely failure modes would include:

- **Short emails with few features**: Several real phishing emails from my spam folder were barely more than a subject line (e.g., the cloud-storage locked emails). With only 4–6 features, weighted probabilities converge toward 0.5 for both categories, making classification unreliable.
- **Shared vocabulary**: Words like `click`, `account`, `free`, and `your` appear in both phishing and other-spam. These weak signals can tip a borderline email the wrong way.
- **Unseen vocabulary**: A phishing email using atypical phrasing like "we require validation of your credentials" instead of "verify your account" would score poorly because those words were never seen in training.

---

## Q3: Confusion Matrix

### Results

|  | **Predicted: Phishing** | **Predicted: Other** |
|---|---|---|
| **Actual: Phishing** | TP = 5 | FN = 0 |
| **Actual: Other** | FP = 0 | TN = 5 |

**Metrics:**
- Precision = TP / (TP + FP) = 5/5 = **1.00**
- Recall = TP / (TP + FN) = 5/5 = **1.00**
- F1-Score = **1.00**
- Accuracy = (TP + TN) / 10 = **100%**

> **Q: Based on the confusion matrix, how well did the classifier perform?**

The classifier achieved perfect performance on the 10-email test set: zero false positives and zero false negatives. This result is plausible because the two spam categories have very distinct vocabularies. Phishing emails consistently use words like *suspended, locked, deleted, verify, account, photos, videos, action, risk, immediate*. Promotional/health spam uses different words: *free, prize, loan, cure, earn, claim, guarantee, reward*. With 20 training documents per class, the classifier developed strong, non-overlapping word-frequency signals. In a real-world deployment with thousands of diverse emails and adversarially crafted messages, 100% accuracy would be far less likely.

> **Q: Would you prefer more false positives or more false negatives? Why?**

For a general spam classifier, **more false negatives** (spam gets through) are preferable to **more false positives** (legitimate email gets filtered). A missed spam is annoying but low-cost: the user sees it and deletes it. A false positive means a real email: a job offer, a university deadline, a bank notification: is silently buried in the spam folder and potentially never read. That asymmetric cost favors tolerating more spam over filtering legitimate mail.

*However*, for a **phishing-specific** classifier like this one, the calculus shifts slightly. A phishing email that gets through could lead to real harm: credential theft, financial loss, or account takeover. In that context, accepting a few extra false positives (aggressively filtering borderline emails) might be worth the tradeoff. The general rule remains: fewer false positives: but phishing classifiers are one area where stricter filtering is justified.

---

## Files

```
HW7/
├── training/
│   ├── spam_phishing/     # 20 on-topic (phishing) training emails
│   └── spam_other/        # 20 off-topic (other spam) training emails
├── testing/
│   ├── spam_phishing/     # 5 on-topic test emails
│   └── spam_other/        # 5 off-topic test emails
└── scripts/
    └── classify_emails.py # Naive Bayes classifier (train + test)
```

---

## References

- Segaran, T. (2007). *Programming Collective Intelligence*. O'Reilly Media. Chapter 6: Document Filtering.
- CS432 / CS532 Web Science: Document Filtering Lecture Slides, Spring 2026. Old Dominion University.
- CS432 Colab Notebook: PCI Chapter 6: Naive Bayes Classifier Examples.
- Emails sourced from a personal spam folder (all sensitive information removed).

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
