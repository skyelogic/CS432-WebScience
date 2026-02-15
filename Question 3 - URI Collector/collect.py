#!/usr/bin/env python3
"""
Multi-Seed Web URI Collector
Collects URIs from multiple seed websites

Usage: python collect-multi-seed.py [target_count]
"""

import sys
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import random
import time

# Disable SSL warnings
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# ========================================
# SEED URLS
# ========================================
SEED_URLS = [
    "http://www.ourgemcodes.com",
    "https://www.ourgemcodes.com/ourworld/adobes-announcement-mean-end-ourworld-animal-jam-kongregate-many-flash-portals/",
    "https://www.ourgemcodes.com/ourworld/ourworld-cracking-sharing-social-media-links-banning-old-accounts/",
    "http://www.donnelgarner.com",
    "http://www.viapist.com",
    "http://www.hostrepair.com",
    "https://www.youtube.com",
    "https://www.medium.com",
    "https://www.imdb.com",
    "https://apps.apple.com",
    "https://play.google.com",
    "https://x.com",
    "https://facebook.com",
    "https://reddit.com",
    "https://en.wikipedia.org",
    "https://gizmodo.com",
    "https://www.britannica.com",
    "https://www.linkedin.com",
    "https://www.pcmag.com",
    "https://weiglemc.github.io/",
    "https://weiglemc.github.io/publications/",
    "https://weiglemc.github.io/teaching/",
    "https://weiglemc.github.io/students/",
    "https://weiglemc.github.io/talks/",
    "https://weiglemc.github.io/schedule/",
    "https://weiglemc.github.io/cv/",
    "https://weiglemc.github.io/contact/",
    "https://www.odu.edu/",
    "https://www.odu.edu/facultydevelopment/women-in-stem#tab9=3&done1612907281342",
    "https://www.odu.edu/computer-science/academics/graduate/masters",
    "https://www.odu.edu/computer-science/academics/graduate/phd",
    "https://oduwsdl.github.io/",
    "https://weiglemc.github.io/publications/recent",
    "https://minerva.defense.gov/Research/Funded-Projects/Article/2957187/innovating-interdisciplinary-methods-for-hard-to-reach-environments/",
    "https://oducsreu.github.io",
    "https://www.odu.edu/computer-science",
    "https://www.clemson.edu/cecas/departments/computing/",
    "https://cs.unc.edu/",
    "https://www.unc.edu/",
    "http://jekyllrb.com/",
    "https://mademistakes.com/work/jekyll-themes/minimal-mistakes/"
]

def is_valid_url(url):
    """Check if URL has a valid scheme"""
    try:
        result = urlparse(url)
        return all([result.scheme, result.netloc]) and result.scheme in ['http', 'https']
    except:
        return False

def get_final_url(url, timeout=3):
    """Get final URL and validate it's HTML with >1000 bytes"""
    try:
        response = requests.head(
            url, 
            timeout=timeout, 
            allow_redirects=True,
            verify=False
        )
        
        content_type = response.headers.get('Content-Type', '').lower()
        
        if 'text/html' not in content_type:
            return (response.url, False)
        
        content_length = response.headers.get('Content-Length')
        
        if content_length is None:
            response = requests.get(url, timeout=timeout, allow_redirects=True, verify=False)
            content_length = len(response.content)
        else:
            content_length = int(content_length)
        
        if content_length > 1000:
            return (response.url, True)
        else:
            return (response.url, False)
            
    except:
        return (None, False)

def extract_links(url, timeout=3):
    """Extract all links from an HTML page"""
    try:
        response = requests.get(url, timeout=timeout, verify=False)
        soup = BeautifulSoup(response.content, 'html.parser')
        
        links = []
        for link in soup.find_all('a', href=True):
            absolute_url = urljoin(url, link['href'])
            if is_valid_url(absolute_url):
                links.append(absolute_url)
        
        return links
    except:
        return []

def collect_from_seed(seed_url, target_per_seed, collected_uris, processed_urls):
    """
    Collect URIs from a single seed
    Returns number of new URIs found
    """
    print(f"\n{'='*60}", file=sys.stderr)
    print(f"Processing seed: {seed_url}", file=sys.stderr)
    print(f"Target for this seed: {target_per_seed}", file=sys.stderr)
    print(f"{'='*60}", file=sys.stderr)
    
    initial_count = len(collected_uris)
    current_url = seed_url
    iterations = 0
    max_iterations_per_seed = target_per_seed * 2
    
    while (len(collected_uris) - initial_count) < target_per_seed and iterations < max_iterations_per_seed:
        iterations += 1
        
        if current_url in processed_urls:
            # Pick random from what we have
            if collected_uris:
                current_url = random.choice(list(collected_uris))
                continue
            else:
                break
        
        processed_urls.add(current_url)
        
        # Check current URL
        final_url, is_valid = get_final_url(current_url)
        if is_valid and final_url and final_url not in collected_uris:
            collected_uris.add(final_url)
            print(final_url)  # Print to stdout
            print(f"  ✓ Added ({len(collected_uris)} total)", file=sys.stderr)
        
        # Extract links
        links = extract_links(current_url)
        
        # Limit links to check
        if len(links) > 30:
            links = random.sample(links, 30)
        
        # Check each link
        for link in links:
            if link in processed_urls:
                continue
            
            if (len(collected_uris) - initial_count) >= target_per_seed:
                break
            
            final_url, is_valid = get_final_url(link)
            
            if is_valid and final_url and final_url not in collected_uris:
                collected_uris.add(final_url)
                print(final_url)
                print(f"  ✓ Added ({len(collected_uris)} total)", file=sys.stderr)
            
            processed_urls.add(link)
            if final_url:
                processed_urls.add(final_url)
            
            time.sleep(0.05)
        
        # Pick new random seed from collected
        if (len(collected_uris) - initial_count) < target_per_seed:
            if collected_uris:
                current_url = random.choice(list(collected_uris))
    
    new_count = len(collected_uris) - initial_count
    print(f"  → Collected {new_count} URIs from this seed", file=sys.stderr)
    return new_count

def main():
    target_count = int(sys.argv[1]) if len(sys.argv) > 1 else 500
    
    print(f"{'='*60}", file=sys.stderr)
    print(f"MULTI-SEED WEB URI COLLECTOR", file=sys.stderr)
    print(f"{'='*60}", file=sys.stderr)
    print(f"Total target: {target_count} unique URIs", file=sys.stderr)
    print(f"Number of seeds: {len(SEED_URLS)}", file=sys.stderr)
    print(f"Target per seed: ~{target_count // len(SEED_URLS)}", file=sys.stderr)
    print(f"{'='*60}", file=sys.stderr)
    
    collected_uris = set()
    processed_urls = set()
    
    # Calculate target per seed
    target_per_seed = (target_count // len(SEED_URLS)) + 20  # +20 for buffer
    
    # Process each seed
    for seed_url in SEED_URLS:
        if len(collected_uris) >= target_count:
            print(f"\n✓ Target reached! ({len(collected_uris)} URIs)", file=sys.stderr)
            break
        
        try:
            collect_from_seed(seed_url, target_per_seed, collected_uris, processed_urls)
        except KeyboardInterrupt:
            print(f"\n\n⚠ Interrupted by user", file=sys.stderr)
            break
        except Exception as e:
            print(f"\n✗ Error with seed {seed_url}: {str(e)}", file=sys.stderr)
            continue
        
        remaining = target_count - len(collected_uris)
        if remaining > 0:
            print(f"\n→ Still need {remaining} more URIs, moving to next seed...", file=sys.stderr)
    
    print(f"\n{'='*60}", file=sys.stderr)
    print(f"COLLECTION COMPLETE", file=sys.stderr)
    print(f"{'='*60}", file=sys.stderr)
    print(f"Total unique URIs collected: {len(collected_uris)}", file=sys.stderr)
    print(f"Target was: {target_count}", file=sys.stderr)
    
    if len(collected_uris) < target_count:
        print(f"\n⚠ WARNING: Only collected {len(collected_uris)} out of {target_count}", file=sys.stderr)
    else:
        print(f"\n✓ SUCCESS: Target reached!", file=sys.stderr)
    
    print(f"{'='*60}", file=sys.stderr)
    
    # Print seed URLs used (for report)
    print(f"\nSeed URLs used:", file=sys.stderr)
    for seed in SEED_URLS:
        print(f"  - {seed}", file=sys.stderr)

if __name__ == "__main__":
    main()
