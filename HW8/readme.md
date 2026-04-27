# HW 8 - Exploring LLMs  

**CS 432 - Web Science | Spring 2026**  
**Name:** Donnel Garner  
**GitHub:** [skyelogic](https://github.com/skyelogic)  

---

## Table of Contents

- [Q1 - Word Vectors](#q1---word-vectors)
  - [Q1.1 - Semantic Similarity](#q11---semantic-similarity)
  - [Q1.2 - Nearest Semantic Associates (Symmetry Check)](#q12---nearest-semantic-associates-symmetry-check)
  - [Q1.3 - Force-Directed Diagram Visualization](#q13---force-directed-diagram-visualization)
- [Q2 - ChatGPT Prompts](#q2---chatgpt-prompts)
- [Q3 - Explain a Topic Using ChatGPT](#q3---explain-a-topic-using-chatgpt)
- [Q4 - Explore Academic References with ChatGPT](#q4---explore-academic-references-with-chatgpt)
- [References](#references)

---

## Q1 - Word Vectors

All word vector tasks used the **English Wikipedia** model on the [WebVectors](http://vectors.nlpl.eu/explore/embeddings/en/) platform.

---

### Q1.1 - Semantic Similarity

**Tool used:** [http://vectors.nlpl.eu/explore/embeddings/en/misc/](http://vectors.nlpl.eu/explore/embeddings/en/misc/)

I used the [Random Noun Generator](https://randomwordgenerator.com/noun.php) to generate two random words: **"bottle"** and **"thunder"**. I entered both into the semantic similarity tool using the English Wikipedia model.

The resulting similarity score was low, which made sense: bottles and thunder don't share much semantic overlap in how they appear across Wikipedia text.  In fact, none at all.

To increase the similarity, I replaced **"thunder"** with **"lightning"**: a word that frequently co-occurs with "storm" and appears in many of the same contexts as weather-related vocabulary. However, I also tried swapping "bottle" with **"storm"** to see if that gave an even better result.

![Semantic Similarity - bottle vs lightning](images/word%20pairs%201.png)  
*Figure 1: Higher similarity score after changing one word to "lightning"*

The better approach turned out to be comparing **"bottle"** with **"jar"**: two words that describe similar physical containers, which the model rated with a much higher cosine similarity score.

![Semantic Similarity - bottle vs jar](images/word%20pairs%202.png)  
*Figure 2: Higher similarity score after changing one word to "jar"*

**Takeaway:** The model captures meaning through co-occurrence in text. Words that appear in similar contexts (containers, storage, kitchen items) score higher than words from entirely different domains.

---

### Q1.2 - Nearest Semantic Associates (Symmetry Check)

**Tool used:** [http://vectors.nlpl.eu/explore/embeddings/en/similar/](http://vectors.nlpl.eu/explore/embeddings/en/similar/)

I generated a random word using the noun generator and got **"violin"**. I searched for its 10 nearest semantic associates using the English Wikipedia model.

![Violin semantic associates](images/violin%20similar%20words.png)  
*Figure 3: 10 nearest semantic associates for "violin"*

The top associate returned was **"cello"**: a closely related string instrument that shares almost all of the same musical, orchestral, and performance contexts.

I then clicked on **"cello"** to check its top associates.

![Cello semantic associates](images/cello%20similar%20words.png)  
*Figure 4: 10 nearest semantic associates for "cello"*

**Was "violin" the top associate for "cello"?**

Yes: "violin" appeared as the top (or near-top) associate for "cello" as well. This symmetry makes sense: both instruments belong to the string family, appear together in orchestra descriptions, music theory articles, and composer biographies throughout Wikipedia. The relationship is mutual and well-represented in the training data.

---

### Q1.3 - Force-Directed Diagram Visualization

**Tool used:** [http://vectors.nlpl.eu/explore/embeddings/en/similar/](http://vectors.nlpl.eu/explore/embeddings/en/similar/)

I generated a new random word: **"glacier"**. After finding its 10 nearest semantic associates (which included words like "iceberg," "snowfield," "moraine," and "ice sheet"), I clicked the generated visualization link: **"Semantic associates for glacier"**: to view the force-directed diagram.

![Glacier force-directed diagram](images/glacier%20vector.png)  
*Figure 5: Force-directed diagram showing semantic neighborhood of "glacier"*

The diagram shows the word "glacier" at the center connected to its top associates, with those associates also showing their own connections. The clustering reflects the word's strong ties to Arctic/Antarctic geography, climate science, and geological terminology: all domains where "glacier" appears frequently in Wikipedia.

---

## Q2 - ChatGPT Prompts

I found the following 5 prompts from the [Awesome ChatGPT Prompts](https://github.com/f/awesome-chatgpt-prompts) GitHub repository, a widely-shared community resource for interesting and creative uses of ChatGPT.

---

### Prompt 1: Job Interviewer

**Source:** [Awesome ChatGPT Prompts - Act as an Interviewer](https://github.com/f/awesome-chatgpt-prompts#act-as-an-interviewer)

**Prompt:**
> I want you to act as an interviewer. I will be the candidate and you will ask me the interview questions for the `Software Developer` position. I want you to only reply as the interviewer. Do not write all the conversation at once. I want you to only do the interview with me. Ask me the questions and wait for my answers. Do not write explanations. Ask me the questions one by one like an interviewer does and wait for my answers. My first sentence is "Hi"

**Result:**

This prompt was surprisingly effective. ChatGPT stayed completely in character as an interviewer and asked realistic technical and behavioral questions one at a time: things like describing a challenging project, explaining how I handle tight deadlines, and walking through my experience with version control. It felt like a genuine mock interview session. The prompt's instruction to *wait for answers* before continuing is the key design choice that makes it work.

**ChatGPT Conversation Link:** (https://chatgpt.com/share/69eedac0-da90-83ea-aff6-943933aa7de6))

---

### Prompt 2: Stand-Up Comedian

**Source:** [Awesome ChatGPT Prompts - Act as a Stand-Up Comedian](https://github.com/f/awesome-chatgpt-prompts#act-as-a-stand-up-comedian)

**Prompt:**
> I want you to act as a stand-up comedian. I will provide you with some topics related to current events and you will use your wit, creativity, and observational skills to create a routine based on those topics. You should also be sure to incorporate personal anecdotes or experiences into the routine in order to make it more relatable and engaging for the audience. My first request is "I want a humorous take on politics."

**Result:**

ChatGPT generated a short comedy set with a few decent observational jokes about political gridlock and the news cycle. Some of the jokes were pretty good: particularly the ones leaning into the absurdity of modern media. It's not quite open-mic night at the Funny Bone, but it was a fun use of the model.

**ChatGPT Conversation Link:** (https://chatgpt.com/share/69eedb11-a42c-83ea-85e8-f819ba37a74e)

---

### Prompt 3: Rapper

**Source:** [Awesome ChatGPT Prompts - Act as a Rapper](https://github.com/f/awesome-chatgpt-prompts#act-as-a-rapper)

**Prompt:**
> I want you to act as a rapper. You will come up with powerful and meaningful lyrics, beats and rhythm that can 'wow' the audience. Your lyrics should have an intriguing meaning and message which people can relate to. When it comes to choosing your beat, make sure it is catchy yet relevant to your words, so that when combined they make an explosion of sound every time! My first request is "I need a rap song about finding strength within yourself."

**Result:**

This one was actually impressive. ChatGPT wrote a full rap with verses and a hook, structured with real rhyme schemes and a consistent theme about inner resilience. Given my background in creating online radio stations for gaming communities, I appreciated that it thought about the rhythm and flow: not just the words. It even suggested a beat style (lo-fi trap with piano samples) to match the tone.

**ChatGPT Conversation Link:** [Rapper GPT](https://chatgpt.com/share/69eedb5c-ea50-83ea-b5ff-1de145b2d9dd)

---

### Prompt 4: Magician

**Source:** [Awesome ChatGPT Prompts - Act as a Magician](https://github.com/f/awesome-chatgpt-prompts#act-as-a-magician)

**Prompt:**
> I want you to act as a magician. I will provide you with an audience and some suggestions for tricks that can be performed. Your goal is to perform these tricks in the most entertaining way possible, using your skills of deception and misdirection to amaze and astound the spectators. My first request is "I want you to make my watch disappear! How can you do that?"

**Result:**

ChatGPT narrated a theatrical magic performance: building suspense, describing the misdirection, and walking through the "disappearance" in a showman's voice. It was genuinely fun to read, even though obviously no watch was actually going anywhere. It highlights how well the model can adopt a persona and commit to a bit.

**ChatGPT Conversation Link:** [Magic GPT](https://chatgpt.com/share/69eedb9f-3af4-83ea-9c15-7a4db2240281)

---

### Prompt 5: Survival Analysis (Personal Context)

**Source:** [Awesome ChatGPT Prompts - Various personal context prompts](https://github.com/f/awesome-chatgpt-prompts)

**Prompt:**
> Based on everything you know about me, if I were left in the middle of a vast forest one day, how long do you think I'd survive? Explain the reasoning behind your guess.

**Result:**

This one was the most interesting of the five because it forced ChatGPT to synthesize what it knows about me from our conversation history. It mentioned my military background as a major survival asset: discipline, situational awareness, training under pressure. It also factored in general physical fitness and prior experience with challenging environments. Its estimate was generous (several weeks with available water and game), and the reasoning was actually pretty thoughtful.

It's a great example of using ChatGPT for reflective, personalized analysis rather than just information retrieval.

**ChatGPT Conversation Link:** [GPT Survival](https://chatgpt.com/share/69eedbe7-e36c-83ea-9f66-7a8bdbbf3385)

---

## Q3 - Explain a Topic Using ChatGPT

**Concept chosen:** PageRank and how it relates to web graph structure

PageRank was one of those topics where I understood the high-level idea (links are votes) but struggled when it came to the actual math: particularly the damping factor and why the algorithm needs to iterate rather than compute directly.

I asked ChatGPT:
> *"Can you explain how PageRank works in a way that makes the math intuitive? I understand that links are like votes, but I get lost when the damping factor and the iterative computation come up."*

**Initial Explanation:**

ChatGPT explained PageRank as a "random surfer" model: imagine someone clicking links randomly on the web. The damping factor (usually 0.85) represents the probability that the surfer keeps clicking versus getting bored and jumping to a random page. That framing clicked immediately: the 0.85 isn't arbitrary, it models real human browsing behavior. The reason it's iterative is because every page's score depends on the scores of all pages linking to it, which themselves depend on *their* incoming links, so you have to keep updating until the scores stabilize (converge).

That explanation helped a lot. The random surfer model is a much more intuitive frame than the matrix algebra version.

**Follow-up question:**

I then asked:
> *"Can you show me a tiny example: like 3 or 4 pages: and walk through two or three iterations manually so I can see how the scores change?"*

ChatGPT set up a simple 4-page graph (A → B, B → C, C → A, A → C) and walked through the initial uniform scores and how they shifted over three iterations. Watching the numbers actually move made the convergence concept concrete. By iteration 3, the scores were already stabilizing, which matched what the lecture slides described.

**Did it help?**

Yes, significantly. The random surfer framing was something the lecture mentioned but didn't dwell on, and having the manual iteration example made the convergence property feel obvious rather than mysterious. I'd recommend this approach: ask for a first explanation, then ask for a concrete small example with numbers.

---

## Q4 - Explore Academic References with ChatGPT

I asked ChatGPT to describe the research of three ODU faculty members and provide 2 of their best known papers. I then verified whether those papers actually exist.

---

### Faculty Member 1: Lauren Sinclair (ODU Dance)

**ChatGPT's Response Summary:**

ChatGPT described Lauren Sinclair as a dance educator and choreographer whose research focuses on dancer wellness, mental health in the performing arts, and somatic approaches to dance pedagogy. It attributed two papers to her:

1. *"Somatic Practices and Mental Health in Collegiate Dance Programs"*: purportedly published in the *Journal of Dance Education*.
2. *"Integrating Mindfulness Into Ballet Pedagogy: A Case Study"*: attributed to the *Research in Dance Education* journal.

**Did the papers exist?**

No. I searched Google Scholar, the ODU Digital Commons, and the *Journal of Dance Education* directly: neither paper appeared. ChatGPT hallucinated both titles. This is a well-known failure mode: the model knows enough about a person's general area to generate plausible-sounding paper titles, but fabricates the specifics.

What is accurate: Lauren Sinclair is a real ODU Dance faculty member and choreographer who works with the University Dance Theatre. She does have a background in dancer wellness and is certified through Danscend (Mental Wellness Certified Dance Educator). However, her public profile reflects a practitioner/performer focus rather than a traditional academic research publication record, which likely explains why ChatGPT had so little real material to draw from.

**Verification method:** Google Scholar search for author name + title, ODU Digital Commons search, direct journal website searches.

---

### Faculty Member 2: Shahrooz Moosavizadeh (ODU Mathematics & Statistics)

**ChatGPT's Response Summary:**

ChatGPT described Dr. Moosavizadeh as a mathematics faculty member specializing in applied and computational mathematics, with a focus on fluid dynamics and magnetohydrodynamics (MHD). It attributed two papers to him:

1. *"Magnetohydrodynamic Stagnation-Point Flow of a Conducting Fluid"*: listed as a journal article.
2. *"Numerical Solutions for MHD Boundary Layer Flow Problems"*: attributed to an applied mathematics journal.

**Did the papers exist?**

Partially. His 1996 doctoral dissertation at ODU: *"Exact Solutions for Orthogonal and Non-Orthogonal Magnetohydrodynamic Stagnation-Point Flow"*: is real and accessible through the ODU Digital Commons (DOI: 10.25777/hqxg-n236). His ResearchGate profile also lists a real publication: *"Steady incompressible magnetohydrodynamic flow near a point of reattachment."*

The specific titles ChatGPT generated don't match the real ones exactly, but the subject matter was accurate: MHD stagnation-point flow is genuinely his area of expertise. This is a case where ChatGPT got the domain right but fabricated the specific citations rather than retrieving real ones.

**Verification method:** ODU Digital Commons, ResearchGate profile search, Google Scholar.

---

### Faculty Member 3: Nasreen Muhammad Arif (ODU Computer Science)

**ChatGPT's Response Summary:**

ChatGPT described Prof. Arif as a Computer Science lecturer at ODU teaching courses including Web Science (CS 432) and Operating Systems. It described her interests as web development, software engineering, and bioelectrics research. It generated two papers:

1. *"Web-Based Learning Environments and Student Engagement in Computer Science Education"*
2. *"Applications of Machine Learning in Bioelectric Signal Processing"*

**Did the papers exist?**

No. Neither paper appeared in Google Scholar or any academic database. Her LinkedIn does reference presenting research at the **Bioelectric Retreat 2024** at ODU's Frank Reidy Research Center for Bioelectrics, which confirms a connection to that research area: but no published paper matching the generated title could be found.

Prof. Arif's ODU profile lists her as a Lecturer (not a research-track professor), and her public presence emphasizes her teaching and software development background across a wide range of languages and frameworks. ChatGPT again produced plausible-sounding but fabricated citations.

**Verification method:** Google Scholar, ODU Digital Commons, LinkedIn profile, ODU faculty directory.

---

### Q4 Summary

| Faculty Member | Department | Papers Exist? | Notes |
|---|---|---|---|
| Lauren Sinclair | Dance | ❌ Both fabricated | Real person, practitioner focus, no traceable academic publications |
| Shahrooz Moosavizadeh | Mathematics & Statistics | ⚠️ Partially | Domain correct (MHD), real dissertation exists, generated titles don't match |
| Nasreen Muhammad Arif | Computer Science | ❌ Both fabricated | Real lecturer, bioelectrics connection confirmed, no matching papers found |

**Key finding:** ChatGPT is prone to hallucinating academic citations, especially for faculty who are practitioners, lecturers, or early-career researchers with limited public publication records. It performs better at describing *what area* someone works in than at accurately citing *what they've published*. Always verify through Google Scholar, institutional repositories, or journal websites directly.

---

## References

- WebVectors Project: [http://vectors.nlpl.eu/explore/embeddings/en/](http://vectors.nlpl.eu/explore/embeddings/en/)
- WebVectors About: [http://vectors.nlpl.eu/explore/embeddings/en/about/](http://vectors.nlpl.eu/explore/embeddings/en/about/)
- Random Noun Generator: [https://randomwordgenerator.com/noun.php](https://randomwordgenerator.com/noun.php)
- Awesome ChatGPT Prompts: [https://github.com/f/awesome-chatgpt-prompts](https://github.com/f/awesome-chatgpt-prompts)
- ODU Dance Faculty: [https://www.odu.edu/commtheatre/dance/faculty](https://www.odu.edu/commtheatre/dance/faculty)
- Moosavizadeh Dissertation (ODU Digital Commons): [https://digitalcommons.odu.edu/mathstat_etds/34/](https://digitalcommons.odu.edu/mathstat_etds/34/)
- Moosavizadeh ResearchGate: [https://www.researchgate.net/profile/Shahrooz-Moosavizadeh](https://www.researchgate.net/profile/Shahrooz-Moosavizadeh)
- ODU CS Faculty Directory: [https://faculty.pages.cs.odu.edu/](https://faculty.pages.cs.odu.edu/)
- ODU Math Directory: [https://www.odu.edu/math/directory](https://www.odu.edu/math/directory)
- HW Report Guidelines: [https://github.com/odu-cs432-websci/public-spr26/blob/main/getting-started/reports.md](https://github.com/odu-cs432-websci/public-spr26/blob/main/getting-started/reports.md)

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
