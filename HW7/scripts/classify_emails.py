# Name: Donnel Garner
# Class: CS432 - Web Science
# Date: April 2026
# HW7 - Email Classification using Naive Bayes

import re
import os

# -----------------------------------------------
# getwords: extracts features (unique words) from a document
# splits on non-letter characters, keeps words 3-20 chars long,
# converts to lowercase, and returns only unique words
# literally from the Google Colab Notebook
# -----------------------------------------------
def getwords(doc):
    splitter = re.compile(r'\W+')
    words = [s.lower() for s in splitter.split(doc)
             if len(s) > 2 and len(s) < 20]
    uniq_words = dict([(w, 1) for w in words])
    return uniq_words


# -----------------------------------------------
# basic_classifier: stores feature/category counts
# and provides core probability methods
# -----------------------------------------------
class basic_classifier:

    def __init__(self, getfeatures):
        self.fc = {}   # feature -> {category -> count}
        self.cc = {}   # category -> count
        self.getfeatures = getfeatures

    def incf(self, f, cat):
        self.fc.setdefault(f, {})
        self.fc[f].setdefault(cat, 0)
        self.fc[f][cat] += 1

    def incc(self, cat):
        self.cc.setdefault(cat, 0)
        self.cc[cat] += 1

    def fcount(self, f, cat):
        if f in self.fc and cat in self.fc[f]:
            return float(self.fc[f][cat])
        return 0.0

    def catcount(self, cat):
        if cat in self.cc:
            return float(self.cc[cat])
        return 0

    def totalcount(self):
        return sum(self.cc.values())

    def categories(self):
        return self.cc.keys()

    def train(self, item, cat):
        features = self.getfeatures(item)
        for f in features:
            self.incf(f, cat)
        self.incc(cat)

    def fprob(self, f, cat):
        if self.catcount(cat) == 0:
            return 0
        return self.fcount(f, cat) / self.catcount(cat)

    def weightedprob(self, f, cat, prf, weight=1.0, ap=0.5):
        basicprob = prf(f, cat)
        totals = sum([self.fcount(f, c) for c in self.categories()])
        bp = ((weight * ap) + (totals * basicprob)) / (weight + totals)
        return bp


# -----------------------------------------------
# naivebayes: extends basic_classifier with full
# Bayesian classification using document probabilities
# -----------------------------------------------
class naivebayes(basic_classifier):

    def __init__(self, getfeatures):
        basic_classifier.__init__(self, getfeatures)
        self.thresholds = {}

    def docprob(self, item, cat):
        features = self.getfeatures(item)
        p = 1
        for f in features:
            p *= self.weightedprob(f, cat, self.fprob)
        return p

    def prob(self, item, cat):
        catprob = self.catcount(cat) / self.totalcount()
        docprob = self.docprob(item, cat)
        return docprob * catprob

    def setthreshold(self, cat, t):
        self.thresholds[cat] = t

    def getthreshold(self, cat):
        if cat not in self.thresholds:
            return 1.0
        return self.thresholds[cat]

    def classify(self, item, default=None):
        probs = {}
        max_prob = 0.0
        best = default
        for cat in self.categories():
            probs[cat] = self.prob(item, cat)
            if probs[cat] > max_prob:
                max_prob = probs[cat]
                best = cat
        # Check threshold: best must exceed threshold * next best
        for cat in probs:
            if cat == best:
                continue
            if probs[cat] * self.getthreshold(best) > probs[best]:
                return default
        return best


# -----------------------------------------------
# Helper: read a text file and return its contents
# -----------------------------------------------
def read_email(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        return f.read()


# -----------------------------------------------
# MAIN
# -----------------------------------------------
BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TRAIN_PHISHING = os.path.join(BASE, 'training', 'spam_phishing')
TRAIN_OTHER    = os.path.join(BASE, 'training', 'spam_other')
TEST_PHISHING  = os.path.join(BASE, 'testing', 'spam_phishing')
TEST_OTHER     = os.path.join(BASE, 'testing', 'spam_other')

# Labels
PHISHING_LABEL = 'phishing'
OTHER_LABEL    = 'other'

# Step 1: Initialize the Naive Bayes classifier
cl = naivebayes(getwords)

# Step 2: Train on all training emails
print("=== TRAINING ===")
phishing_files = sorted(os.listdir(TRAIN_PHISHING))
other_files    = sorted(os.listdir(TRAIN_OTHER))

for fname in phishing_files:
    text = read_email(os.path.join(TRAIN_PHISHING, fname))
    cl.train(text, PHISHING_LABEL)
    print(f"  Trained [phishing]: {fname}")

for fname in other_files:
    text = read_email(os.path.join(TRAIN_OTHER, fname))
    cl.train(text, OTHER_LABEL)
    print(f"  Trained [other]:    {fname}")

print(f"\nTraining complete. {int(cl.catcount(PHISHING_LABEL))} phishing, "
      f"{int(cl.catcount(OTHER_LABEL))} other docs trained.\n")

# Step 3: Test on all testing emails
print("=== TESTING ===")
results = []

test_cases = (
    [(f, PHISHING_LABEL) for f in sorted(os.listdir(TEST_PHISHING))] +
    [(f, OTHER_LABEL)    for f in sorted(os.listdir(TEST_OTHER))]
)

for fname, true_label in test_cases:
    folder = TEST_PHISHING if true_label == PHISHING_LABEL else TEST_OTHER
    text = read_email(os.path.join(folder, fname))
    predicted = cl.classify(text, default='unknown')
    correct = (predicted == true_label)
    results.append({
        'file': fname,
        'true': true_label,
        'predicted': predicted,
        'correct': correct
    })
    status = 'CORRECT' if correct else 'WRONG'
    print(f"  [{status}] {fname}: predicted={predicted}, actual={true_label}")

# Step 4: Summary table
print("\n=== RESULTS TABLE ===")
print(f"{'Email File':<30} {'Actual':<12} {'Predicted':<12} {'Correct?'}")
print("-" * 65)
for r in results:
    print(f"{r['file']:<30} {r['true']:<12} {r['predicted']:<12} {'Yes' if r['correct'] else 'No'}")

correct_count = sum(1 for r in results if r['correct'])
print(f"\nAccuracy: {correct_count}/{len(results)} = {correct_count/len(results)*100:.1f}%")

# Step 5: Confusion matrix values
tp = sum(1 for r in results if r['true'] == PHISHING_LABEL and r['predicted'] == PHISHING_LABEL)
tn = sum(1 for r in results if r['true'] == OTHER_LABEL    and r['predicted'] == OTHER_LABEL)
fp = sum(1 for r in results if r['true'] == OTHER_LABEL    and r['predicted'] == PHISHING_LABEL)
fn = sum(1 for r in results if r['true'] == PHISHING_LABEL and r['predicted'] == OTHER_LABEL)

print(f"\n=== CONFUSION MATRIX ===")
print(f"                  Predicted Phishing  Predicted Other")
print(f"Actual Phishing   TP={tp}               FN={fn}")
print(f"Actual Other      FP={fp}               TN={tn}")
