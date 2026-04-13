# HW6 - Recommendation Systems

**Course:** CS 432 / 532 - Web Science (Spring 2026)  
**Instructor:** Nasreen Muhammad Arif  
**Student:** Donnel Garner | [skyelogic](https://github.com/skyelogic)  
**Due:** April 12, 2026

---

## Table of Contents

- [Overview](#overview)
- [Dataset](#dataset)
- [Setup & Running](#setup--running)
- [Q1: Finding a Substitute User](#q1-finding-a-substitute-user)
- [Q2: User Correlations](#q2-user-correlations)
- [Q3: Film Recommendations](#q3-film-recommendations)
- [Q4: Item-Based Correlations](#q4-item-based-correlations)
- [Technologies](#technologies)  
- [References](#references)  

---

## Overview

This assignment applies **collaborative filtering** techniques from *Programming Collective Intelligence* (Chapter 2) to the MovieLens 100K dataset. The goal is to:

1. Identify demographically similar users to act as a "substitute me"
2. Find users whose movie ratings correlate most (and least) with the substitute
3. Generate personalized movie recommendations for the substitute
4. Explore item-based similarity using my own favorite and least favorite films

---

## Dataset

**MovieLens 100K**: collected by the GroupLens Research Project, University of Minnesota (Sep 1997 – Apr 1998)

| File | Description |
|------|-------------|
| `u.data` | 100,000 ratings by 943 users on 1,682 movies (tab-separated: user, item, rating, timestamp) |
| `u.item` | Movie metadata including title, release date, genre flags |
| `u.user` | Demographic info: age, gender, occupation, zip code |

Download: https://grouplens.org/datasets/movielens/100k/

---

## Setup & Running

**Requirements:** Python 3.6+, no external libraries needed

```bash
# 1. Extract the dataset
unzip ml-100k.zip

# 2. Run the full analysis
python3 hw6_analysis.py
```

**Python 2 → 3 fix applied to `recommendations.py`:**  
Changed `print '%d / %d'` → `print('%d / %d')` in `calculateSimilarItems()`.

---

## Q1: Finding a Substitute User

![Question 1 Answer](images/Question%201.png)

Searched `u.user` for male users aged 20–28 with technical occupations (technician, programmer, engineer). Three candidates:

| User ID | Age | Gender | Occupation | Films Rated |
|---------|-----|--------|------------|-------------|
| 1 | 24 | M | Technician | 271 |
| 4 | 24 | M | Technician | 24 |
| 53 | 26 | M | Programmer | 28 |

<details>
<summary><strong>User 1: Top 3 & Bottom 3 Films</strong></summary>

**Top 3 (5.0 ★):**
| Rank | Film |
|------|------|
| 1 | 12 Angry Men (1957) |
| 2 | Alien (1979) |
| 3 | Aliens (1986) |

**Bottom 3 (1.0 ★):**
| Rank | Film |
|------|------|
| 1 | Air Bud (1997) |
| 2 | All Dogs Go to Heaven 2 (1996) |
| 3 | Babe (1995) |

</details>

<details>
<summary><strong>User 4: Top 3 & Bottom 3 Films</strong></summary>

**Top 3 (5.0 ★):**
| Rank | Film |
|------|------|
| 1 | Air Force One (1997) |
| 2 | Assignment, The (1997) |
| 3 | Blues Brothers 2000 (1998) |

**Bottom 3:**
| Rank | Film | Rating |
|------|------|--------|
| 1 | Spawn (1997) | 2.0 |
| 2 | Client, The (1994) | 3.0 |
| 3 | Conspiracy Theory (1997) | 3.0 |

</details>

<details>
<summary><strong>User 53: Top 3 & Bottom 3 Films</strong></summary>

**Top 3 (5.0 ★):**
| Rank | Film |
|------|------|
| 1 | Bridge on the River Kwai, The (1957) |
| 2 | Fargo (1996) |
| 3 | Mr. Holland's Opus (1995) |

**Bottom 3:**
| Rank | Film | Rating |
|------|------|--------|
| 1 | Fifth Element, The (1997) | 2.0 |
| 2 | Saint, The (1997) | 2.0 |
| 3 | Tin Cup (1996) | 2.0 |

</details>

**Chosen Substitute: User 1**

User 1 is a 24-year-old male technician: the closest demographic match. Their taste aligns well: high ratings for classic sci-fi (Alien, Aliens) and court dramas (12 Angry Men), clear rejection of children's films (Air Bud, Babe). Their 271-film rating set also makes them the most statistically reliable substitute. *Outlier note: I would not have rated Groundhog Day as highly: more of a 3 for me: but overall User 1 is my best match.*

---

## Q2: User Correlations

![Question 2 Answer](images/Question%202.png)

Correlation computed using `sim_pearson()` from `recommendations.py`.

**Top 5 Most Correlated to User 1:**

| User ID | Pearson Score | Age | Gender | Occupation |
|---------|--------------|-----|--------|------------|
| 866 | 1.0000 | 45 | M | Other |
| 812 | 1.0000 | 22 | M | Technician |
| 811 | 1.0000 | 40 | F | Educator |
| 810 | 1.0000 | 55 | F | Other |
| 531 | 1.0000 | 30 | F | Salesman |

**Bottom 5 Least Correlated (Negative) to User 1:**

| User ID | Pearson Score | Age | Gender | Occupation |
|---------|--------------|-----|--------|------------|
| 431 | -1.0000 | 24 | M | Marketing |
| 47 | -1.0000 | 53 | M | Marketing |
| 681 | -1.0000 | 44 | F | Marketing |
| 143 | -0.9449 | 42 | M | Technician |
| 36 | -0.9449 | 19 | F | Student |

> **Notable pattern:** The three most negatively correlated users are all in **marketing**: suggesting a consistent taste divergence between tech-oriented users and marketing professionals in this dataset.

### Functions Used

**`sim_pearson(prefs, p1, p2)`**: Computes the Pearson correlation coefficient between two users based on their shared movie ratings. Think of it as measuring whether two people's rating "curves" move together. If both rate the same movies high/low relative to average, the score approaches +1.0; if they consistently disagree, it approaches -1.0. The function identifies mutually rated films, then applies the standard Pearson *r* formula.

**`topMatches(prefs, person, n, similarity)`**: Computes a similarity score between the target user and every other user, collects results as (score, user) pairs, sorts descending, and returns the top *n*. Used here to find the most correlated users efficiently.

---

## Q3: Film Recommendations

![Question 2 Answer](images/Question%203.png)

Recommendations generated using `getRecommendations()` from `recommendations.py`.

**Top 5 Recommended Films (User 1 Should See):**

| Rank | Film | Predicted Rating |
|------|------|-----------------|
| 1 | They Made Me a Criminal (1939) | 5.00 |
| 2 | Star Kid (1997) | 5.00 |
| 3 | Someone Else's America (1995) | 5.00 |
| 4 | Saint of Fort Washington, The (1993) | 5.00 |
| 5 | Prefontaine (1997) | 5.00 |

**Bottom 5 Recommended Films (User 1 Will Likely Dislike):**

| Rank | Film | Predicted Rating |
|------|------|-----------------|
| 1 | August (1996) | 1.00 |
| 2 | Amityville: Dollhouse (1996) | 1.00 |
| 3 | Amityville: A New Generation (1993) | 1.00 |
| 4 | Amityville 1992: It's About Time (1992) | 1.00 |
| 5 | 3 Ninjas: High Noon At Mega Mountain (1998) | 1.00 |

> **Note on 5.0/1.0 edge cases:** These results reflect a sparse-data artifact. Obscure films rated by only a few users who happen to be highly correlated with User 1 can yield perfect predicted scores. A production system would apply a minimum vote threshold to filter these out.

### Function Used

**`getRecommendations(prefs, person, similarity)`**: For every film the substitute user hasn't seen, this function collects ratings from all other users and weights each rating by how correlated that user is to the target. The predicted score for each unseen film is the weighted average. Only positively correlated users contribute (negative correlators are excluded). Results are returned sorted highest-to-lowest.

---

## Q4: Item-Based Correlations

![Question 2 Answer](images/Question%204.png)

My (the real me) choices from the dataset:

- **Favorite:** *Star Wars (1977)*: a foundational sci-fi/action film
- **Least Favorite:** *Ace Ventura: Pet Detective (1994)*: slapstick comedy that doesn't land for me

### Star Wars (1977): Most Correlated Films

| Rank | Film | Pearson Score |
|------|------|--------------|
| 1 | Cosi (1996) | 1.0000 |
| 2 | No Escape (1994) | 1.0000 |
| 3 | Commandments (1997) | 1.0000 |
| 4 | Designated Mourner, The (1997) | 1.0000 |
| 5 | Hollow Reed (1996) | 1.0000 |

### Star Wars (1977): Least Correlated Films

| Rank | Film | Pearson Score |
|------|------|--------------|
| 1 | Roseanna's Grave (For Roseanna) (1997) | -1.0000 |
| 2 | Year of the Horse (1997) | -1.0000 |
| 3 | I Like It Like That (1994) | -1.0000 |
| 4 | American Dream (1990) | -1.0000 |
| 5 | Bewegte Mann, Der (1994) | -1.0000 |

### Ace Ventura: Pet Detective (1994): Most Correlated Films

| Rank | Film | Pearson Score |
|------|------|--------------|
| 1 | Wonderful, Horrible Life of Leni Riefenstahl, The (1993) | 1.0000 |
| 2 | When the Cats Away (Chacun cherche son chat) (1996) | 1.0000 |
| 3 | Welcome To Sarajevo (1997) | 1.0000 |
| 4 | U.S. Marshalls (1998) | 1.0000 |
| 5 | Shiloh (1997) | 1.0000 |

### Ace Ventura: Pet Detective (1994): Least Correlated Films

| Rank | Film | Pearson Score |
|------|------|--------------|
| 1 | Rent-a-Kid (1995) | -1.0000 |
| 2 | True Crime (1995) | -1.0000 |
| 3 | 1-900 (1994) | -1.0000 |
| 4 | Across the Sea of Time (1995) | -1.0000 |
| 5 | Angel and the Badman (1947) | -1.0000 |

### Do the Results Match My Taste?

The all-1.0 correlations reflect a sparse-data limitation (too few shared raters). Searching trailers for the Star Wars correlates (e.g., [Cosi 1996 trailer search](https://www.youtube.com/results?search_query=Cosi+1996+trailer)) revealed quiet independent dramas: not films I would enjoy. The mathematical correlation reflects *rating behavior*, not genre similarity.

The negative correlates for Ace Ventura make more intuitive sense: films like *Angel and the Badman (1947)* attract a fundamentally different audience than 90s slapstick: people who love one tend not to rate the other the same way.

### Functions Used

**`transformPrefs(prefs)`**: Inverts the preference dictionary from `{user: {movie: rating}}` to `{movie: {user: rating}}`, allowing movie-to-movie similarity to be computed using the same functions built for user-to-user comparisons.

**`topMatches(moviePrefs, targetMovie, n)`**: Applied to the inverted dictionary to find movies whose rating patterns across users are most similar to the target movie. A Pearson score near +1.0 means users who liked one tended to like the other; near -1.0 means they disagreed.

---

## Technologies

| Tool | Purpose |
|------|---------|
| Python 3 | Main analysis language |
| `recommendations.py` | Collaborative filtering functions (Ch.2, PCI) |
| MovieLens 100K | Rating dataset |

---

## References

- Segaran, T. (2007). *Programming Collective Intelligence*. O'Reilly.  
  Source: https://github.com/arthur-e/Programming-Collective-Intelligence/blob/master/chapter2/recommendations.py
- GroupLens Research. *MovieLens 100K Dataset*. https://grouplens.org/datasets/movielens/100k/
- CS 432 Course Notebook with examples

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
