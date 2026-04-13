#!/usr/bin/env python3
# Name: Donnel Garner
# Class: CS432 - Web Science
# Date: April 12, 2026
# Description: HW6 - Recommendation Systems using MovieLens 100K dataset.
#              Answers Q1-Q4 by applying collaborative filtering from
#              recommendations.py (adapted from Programming Collective Intelligence, Ch.2).

import sys
sys.path.insert(0, '.')
import recommendations as rec

DATA_PATH = './ml-100k'

def load_data():
    """
    Load MovieLens dataset as { user_id: { movie_title: rating } }.
    Python-3-compatible rewrite of loadMovieLens() from recommendations.py.
    u.item uses latin-1 encoding for special characters in some titles.
    """
    movies = {}
    for line in open(DATA_PATH + '/u.item', encoding='latin-1'):
        parts = line.split('|')
        movies[parts[0]] = parts[1]
    prefs = {}
    for line in open(DATA_PATH + '/u.data'):
        (user, movieid, rating, ts) = line.split('\t')
        prefs.setdefault(user, {})
        prefs[user][movies[movieid]] = float(rating)
    return prefs

def get_user_info(user_id):
    """Return demographic info list for a given user ID from u.user."""
    with open(DATA_PATH + '/u.user') as f:
        for line in f:
            parts = line.strip().split('|')
            if parts[0] == str(user_id):
                return parts
    return None

def top_bottom_films(prefs, user, n=3):
    """Return top-n and bottom-n rated films for a user (ties broken alphabetically)."""
    rated = sorted(prefs[user].items(), key=lambda x: (-x[1], x[0]))
    top = rated[:n]
    bottom = sorted(prefs[user].items(), key=lambda x: (x[1], x[0]))[:n]
    return top, bottom

if __name__ == '__main__':
    print("Loading MovieLens 100K dataset...")
    prefs = load_data()
    print(f"  {len(prefs)} users, {sum(len(v) for v in prefs.values())} total ratings.\n")

    # Q1 -------------------------------------------------------------------
    candidate_users = ['1', '4', '53']
    print("=" * 65)
    print("Q1 - Candidate User Film Analysis")
    print("=" * 65)
    for uid in candidate_users:
        info = get_user_info(uid)
        top3, bot3 = top_bottom_films(prefs, uid)
        print(f"\nUser {uid} | Age:{info[1]} Gender:{info[2]} Occupation:{info[3]}")
        print(f"  Rated: {len(prefs[uid])} films")
        print("  Top 3:   " + " | ".join(f"{t} ({r})" for t,r in top3))
        print("  Bottom 3:" + " | ".join(f"{t} ({r})" for t,r in bot3))
    print("\n-> Chosen substitute: User 1\n")

    # Q2 -------------------------------------------------------------------
    SUBSTITUTE = '1'
    print("=" * 65)
    print(f"Q2 - User Correlations for Substitute (User {SUBSTITUTE})")
    print("=" * 65)

    # sim_pearson() measures how similarly two users rate shared movies.
    # +1.0 = identical taste profile, -1.0 = perfectly opposite tastes.
    all_scores = [(rec.sim_pearson(prefs, SUBSTITUTE, other), other)
                  for other in prefs if other != SUBSTITUTE]
    all_scores.sort(reverse=True)
    top5 = all_scores[:5]
    neg = [s for s in all_scores if s[0] < 0]
    neg.sort()
    bot5 = neg[:5]

    print(f"\nTop 5 most correlated to User {SUBSTITUTE}:")
    for score, uid in top5:
        info = get_user_info(uid)
        print(f"  {score:.4f}  User {uid} | {info[1]}/{info[2]}/{info[3]}")

    print(f"\nBottom 5 least correlated to User {SUBSTITUTE}:")
    for score, uid in bot5:
        info = get_user_info(uid)
        print(f"  {score:.4f}  User {uid} | {info[1]}/{info[2]}/{info[3]}")

    # Q3 -------------------------------------------------------------------
    print("\n" + "=" * 65)
    print(f"Q3 - Recommendations for User {SUBSTITUTE}")
    print("=" * 65)

    # getRecommendations() predicts ratings for unseen films using a weighted
    # average of correlated users' ratings. Higher correlation = higher weight.
    recs = rec.getRecommendations(prefs, SUBSTITUTE)
    print("\nTop 5 (should see):")
    for score, title in recs[:5]:
        print(f"  {score:.4f}  {title}")
    print("\nBottom 5 (will probably dislike):")
    for score, title in recs[-5:]:
        print(f"  {score:.4f}  {title}")

    # Q4 -------------------------------------------------------------------
    FAVORITE  = 'Star Wars (1977)'
    LEAST_FAV = 'Ace Ventura: Pet Detective (1994)'

    print("\n" + "=" * 65)
    print("Q4 - Item-Based Correlations")
    print("=" * 65)

    # transformPrefs() flips {user:{movie:rating}} to {movie:{user:rating}},
    # letting us apply user-similarity functions to find movie-movie similarity.
    movies_prefs = rec.transformPrefs(prefs)

    print(f"\nTop 5 correlated to FAVORITE '{FAVORITE}':")
    for score, title in rec.topMatches(movies_prefs, FAVORITE, n=5):
        print(f"  {score:.4f}  {title}")

    print(f"\nBottom 5 anti-correlated to FAVORITE '{FAVORITE}':")
    all_fav = [(rec.sim_pearson(movies_prefs, FAVORITE, m), m)
               for m in movies_prefs if m != FAVORITE]
    all_fav.sort()
    for score, title in all_fav[:5]:
        print(f"  {score:.4f}  {title}")

    print(f"\nTop 5 correlated to LEAST FAVORITE '{LEAST_FAV}':")
    for score, title in rec.topMatches(movies_prefs, LEAST_FAV, n=5):
        print(f"  {score:.4f}  {title}")

    print(f"\nBottom 5 anti-correlated to LEAST FAVORITE '{LEAST_FAV}':")
    all_least = [(rec.sim_pearson(movies_prefs, LEAST_FAV, m), m)
                 for m in movies_prefs if m != LEAST_FAV]
    all_least.sort()
    for score, title in all_least[:5]:
        print(f"  {score:.4f}  {title}")

    print("\nDone.")
