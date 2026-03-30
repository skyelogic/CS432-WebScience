import networkx as nx
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import numpy as np
from collections import defaultdict
import os

os.makedirs('/home/graphs', exist_ok=True)

# ─────────────────────────────────────────────
# KARATE CLUB DATA
# ─────────────────────────────────────────────
G = nx.karate_club_graph()

# Ground truth clubs from NetworkX
# 'Mr. Hi' = 0, 'Officer' = John A
john_nodes = [n for n, d in G.nodes(data=True) if d['club'] == 'Officer']
mrhi_nodes = [n for n, d in G.nodes(data=True) if d['club'] == 'Mr. Hi']

print(f"John A (Officer) nodes ({len(john_nodes)}): {sorted(john_nodes)}")
print(f"Mr. Hi nodes ({len(mrhi_nodes)}): {sorted(mrhi_nodes)}")

# ─────────────────────────────────────────────
# CONSISTENT LAYOUT
# ─────────────────────────────────────────────
pos = nx.spring_layout(G, seed=42, k=1.8)

def node_colors(G, highlight_partition=None):
    """Return color list: blue=Mr. Hi, red=John A"""
    colors = []
    for n in G.nodes():
        club = G.nodes[n]['club']
        if club == 'Mr. Hi':
            colors.append('#4C72B0')  # blue
        else:
            colors.append('#DD4444')  # red
    return colors

# ─────────────────────────────────────────────
# Q1: ORIGINAL GRAPH COLORED BY FACTION
# ─────────────────────────────────────────────
fig, ax = plt.subplots(figsize=(12, 9))
colors = node_colors(G)

nx.draw_networkx_edges(G, pos, ax=ax, alpha=0.4, edge_color='#888888', width=1.0)
nx.draw_networkx_nodes(G, pos, ax=ax, node_color=colors, node_size=500, linewidths=1.5,
                        edgecolors='white')
nx.draw_networkx_labels(G, pos, ax=ax, font_size=8, font_color='white', font_weight='bold')

legend_elements = [
    mpatches.Patch(facecolor='#4C72B0', label=f'Mr. Hi ({len(mrhi_nodes)} members)'),
    mpatches.Patch(facecolor='#DD4444', label=f'John A / Officer ({len(john_nodes)} members)')
]
ax.legend(handles=legend_elements, loc='upper left', fontsize=11)
ax.set_title("Zachary's Karate Club – Original Graph\n(Colored by Faction of Actual Split)", fontsize=14, fontweight='bold')
ax.axis('off')
plt.tight_layout()
plt.savefig('/home/graphs/q1_original_graph.png', dpi=150, bbox_inches='tight', facecolor='white')
plt.close()
print("Saved Q1 graph")

# ─────────────────────────────────────────────
# GIRVAN-NEWMAN IMPLEMENTATION
# ─────────────────────────────────────────────

def edge_betweenness(G):
    """Compute edge betweenness centrality for all edges."""
    betweenness = dict.fromkeys(G.edges(), 0.0)
    # Undirected: add both orderings
    betweenness.update({(v, u): 0.0 for u, v in G.edges()})

    for s in G.nodes():
        # BFS from source s
        S = []          # stack of nodes in order of non-increasing distance
        P = defaultdict(list)   # predecessors
        sigma = defaultdict(float)  # number of shortest paths
        d = defaultdict(lambda: -1)  # distance
        sigma[s] = 1.0
        d[s] = 0
        Q = [s]
        while Q:
            v = Q.pop(0)
            S.append(v)
            for w in G.neighbors(v):
                # First visit?
                if d[w] < 0:
                    Q.append(w)
                    d[w] = d[v] + 1
                # Shortest path to w via v?
                if d[w] == d[v] + 1:
                    sigma[w] += sigma[v]
                    P[w].append(v)

        # Back-propagation
        delta = defaultdict(float)
        while S:
            w = S.pop()
            for v in P[w]:
                c = (sigma[v] / sigma[w]) * (1 + delta[w])
                if (v, w) in betweenness:
                    betweenness[(v, w)] += c
                else:
                    betweenness[(w, v)] += c
                delta[v] += c

    # Normalise for undirected graph
    for e in betweenness:
        betweenness[e] /= 2.0
    return betweenness


def girvan_newman_step(G):
    """Remove the edge with highest betweenness. Returns removed edge and its score."""
    bet = edge_betweenness(G)
    max_edge = max(bet, key=bet.get)
    max_score = bet[max_edge]
    # Ensure canonical order
    u, v = max_edge
    if not G.has_edge(u, v):
        u, v = v, u
    G.remove_edge(u, v)
    return (u, v), max_score


def draw_graph_iteration(G_iter, iteration, removed_edges, ax_title, filename, pos):
    """Draw a graph with current edges, faded removed edges."""
    fig, ax = plt.subplots(figsize=(12, 9))
    colors = node_colors(G_iter)

    # Draw removed edges in faint dashed style
    if removed_edges:
        nx.draw_networkx_edges(G_iter.__class__(), pos, edgelist=removed_edges,
                                ax=ax, style='dashed', edge_color='#cccccc',
                                alpha=0.35, width=1.0)

    # Current edges
    nx.draw_networkx_edges(G_iter, pos, ax=ax, alpha=0.5, edge_color='#555555', width=1.2)
    nx.draw_networkx_nodes(G_iter, pos, ax=ax, node_color=colors,
                            node_size=500, linewidths=1.5, edgecolors='white')
    nx.draw_networkx_labels(G_iter, pos, ax=ax, font_size=8, font_color='white', font_weight='bold')

    components = list(nx.connected_components(G_iter))
    legend_elements = [
        mpatches.Patch(facecolor='#4C72B0', label='Mr. Hi'),
        mpatches.Patch(facecolor='#DD4444', label='John A / Officer'),
    ]
    ax.legend(handles=legend_elements, loc='upper left', fontsize=10)
    ax.set_title(ax_title, fontsize=13, fontweight='bold')
    ax.axis('off')
    plt.tight_layout()
    plt.savefig(filename, dpi=150, bbox_inches='tight', facecolor='white')
    plt.close()


# ─────────────────────────────────────────────
# Q2: RUN GIRVAN-NEWMAN UNTIL 2 COMPONENTS
# ─────────────────────────────────────────────
GN = G.copy()
removed_edges = []
iteration = 0
iteration_log = []

print("\nRunning Girvan-Newman...")
while nx.number_connected_components(GN) < 2:
    iteration += 1
    edge, score = girvan_newman_step(GN)
    removed_edges.append(edge)
    n_comp = nx.number_connected_components(GN)
    print(f"  Iteration {iteration}: removed edge {edge}, betweenness={score:.4f}, components={n_comp}")
    iteration_log.append({'iter': iteration, 'edge': edge, 'score': score, 'components': n_comp})

    title = (f"Girvan-Newman – Iteration {iteration}\n"
             f"Removed edge {edge} (betweenness={score:.4f}) | "
             f"Connected components: {n_comp}")
    fname = f'/home/graphs/gn_iter_{iteration:02d}.png'
    draw_graph_iteration(GN, iteration, removed_edges.copy(), title, fname, pos)

print(f"\nGraph split into 2 components after {iteration} iterations")

# Final components
components = list(nx.connected_components(GN))
print(f"\nComponent 1 ({len(components[0])} nodes): {sorted(components[0])}")
print(f"Component 2 ({len(components[1])} nodes): {sorted(components[1])}")

# ─────────────────────────────────────────────
# Q3: COMPARISON GRAPH
# ─────────────────────────────────────────────
john_set = set(john_nodes)
mrhi_set = set(mrhi_nodes)

# Map each GN component to a faction label by majority
def component_faction(comp):
    n_john = len(comp & john_set)
    n_mrhi = len(comp & mrhi_set)
    return 'John A' if n_john >= n_mrhi else 'Mr. Hi'

gn_comp_factions = [(c, component_faction(c)) for c in components]

mismatches = []
for comp, faction in gn_comp_factions:
    for node in comp:
        actual = G.nodes[node]['club']
        pred_faction = faction
        actual_faction = 'Mr. Hi' if actual == 'Mr. Hi' else 'John A'
        if actual_faction != pred_faction:
            mismatches.append((node, actual_faction, pred_faction))

print(f"\nMismatched nodes: {mismatches}")

# Draw comparison
fig, axes = plt.subplots(1, 2, figsize=(20, 9))

# Left: actual split
ax = axes[0]
colors = node_colors(G)
nx.draw_networkx_edges(G, pos, ax=ax, alpha=0.4, edge_color='#888888', width=1.0)
nx.draw_networkx_nodes(G, pos, ax=ax, node_color=colors, node_size=500, linewidths=1.5, edgecolors='white')
nx.draw_networkx_labels(G, pos, ax=ax, font_size=8, font_color='white', font_weight='bold')
ax.set_title("Actual Split (Ground Truth)", fontsize=13, fontweight='bold')
legend_elements = [
    mpatches.Patch(facecolor='#4C72B0', label='Mr. Hi'),
    mpatches.Patch(facecolor='#DD4444', label='John A / Officer')
]
ax.legend(handles=legend_elements, loc='upper left', fontsize=10)
ax.axis('off')

# Right: GN result — color by which GN component they ended in
# GN component that matches "Mr. Hi" majority → blue; other → red
mrhi_comp = components[0] if component_faction(components[0]) == 'Mr. Hi' else components[1]
john_comp = components[0] if component_faction(components[0]) == 'John A' else components[1]

gn_colors = []
for n in GN.nodes():
    if n in mrhi_comp:
        gn_colors.append('#4C72B0')
    else:
        gn_colors.append('#DD4444')

# Highlight mismatches with a thick border
mismatch_nodes = [m[0] for m in mismatches]

ax2 = axes[1]
nx.draw_networkx_edges(GN, pos, ax=ax2, alpha=0.5, edge_color='#555555', width=1.2)
nx.draw_networkx_nodes(GN, pos, ax=ax2, node_color=gn_colors, node_size=500,
                        linewidths=1.5, edgecolors='white')
# Highlight mismatches
if mismatch_nodes:
    nx.draw_networkx_nodes(GN, pos, nodelist=mismatch_nodes, ax=ax2,
                            node_color=[gn_colors[list(GN.nodes()).index(n)] for n in mismatch_nodes],
                            node_size=700, linewidths=4, edgecolors='#FFD700')
nx.draw_networkx_labels(GN, pos, ax=ax2, font_size=8, font_color='white', font_weight='bold')
ax2.set_title(f"Girvan-Newman Split (after {iteration} iterations)\n"
               f"Gold border = mismatch with actual split", fontsize=13, fontweight='bold')
legend_elements2 = [
    mpatches.Patch(facecolor='#4C72B0', label='GN: Mr. Hi component'),
    mpatches.Patch(facecolor='#DD4444', label='GN: John A component'),
    mpatches.Patch(facecolor='gray', edgecolor='#FFD700', linewidth=3, label='Mismatched node')
]
ax2.legend(handles=legend_elements2, loc='upper left', fontsize=10)
ax2.axis('off')

plt.suptitle("Q3: Actual Split vs. Girvan-Newman Predicted Split", fontsize=15, fontweight='bold')
plt.tight_layout()
plt.savefig('/home/graphs/q3_comparison.png', dpi=150, bbox_inches='tight', facecolor='white')
plt.close()
print("Saved Q3 comparison graph")

# ─────────────────────────────────────────────
# BETWEENNESS TABLE (for report)
# ─────────────────────────────────────────────
# Show initial top-10 edge betweenness
initial_bet = edge_betweenness(G)
# Collapse to unique edges
unique_bet = {}
for (u, v), score in initial_bet.items():
    key = (min(u, v), max(u, v))
    unique_bet[key] = score
top10 = sorted(unique_bet.items(), key=lambda x: x[1], reverse=True)[:10]
print("\nTop-10 initial edge betweenness:")
for edge, score in top10:
    print(f"  {edge}: {score:.4f}")

print("\nDONE. Iteration summary:")
for rec in iteration_log:
    print(f"  Iter {rec['iter']}: edge={rec['edge']}, bet={rec['score']:.4f}, comps={rec['components']}")