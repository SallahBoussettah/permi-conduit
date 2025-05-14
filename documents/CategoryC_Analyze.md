# Analysis: Category C Driving Exam & Platform Roadmap

This document analyzes the "Épreuve Hors Circulation - Catégorie C" (from `CategoryC.pdf`) and outlines how the upcoming Laravel-based web platform (per `roadmap.md`) will cover the official requirements for candidates and inspectors.

---

## 🧪 Exam Breakdown (Based on `CategoryC.pdf`)

The off-road driving exam ("Plateau") consists of several structured parts:

### 1. Written & Oral Knowledge Checks
| Section | Description | Score Type |
|--------|-------------|------------|
| **QCM** | 10-question multiple-choice (6 minutes) | 0–3 or Eliminatory |
| **Socle Minimum 1** | Pre-driving checks (immobilisation, feux, documents, etc.) | 0/1 points per item |
| **Theme (tiré au sort)** | 1 of 6 oral technical topics (e.g., documents, lights, cargo) | 0–3 points |
| **Interrogation Orale (I.O.)** | Safety and theoretical knowledge questions | 0–3 or Eliminatory |
| **Socle Minimum 2** | Post-theme checks (braking systems, engine start, posture) | 0/1 or 0–3 per item |

### 2. Practical Maneuvers
| Section | Description | Score |
|--------|-------------|--------|
| **Manoeuvres (M1, M2)** | Reversing, turning, etc. | BON / ECHEC |
| **Gestes et Postures** | Safe movement and handling | 0 or 1 |

### 3. Evaluation Criteria
- **Pass threshold:** >16 points overall, no eliminatory scores, favorable maneuver result.
- **Inspector scores** live during the exam using detailed criteria.

---

## 🛠 Platform Features (From `roadmap.md`)

### 🎯 Core Objectives
- Digital reproduction of the "Épreuve Hors Circulation".
- Enable **candidates** to learn, train, and view results.
- Enable **inspectors** to manage exams, track sessions, and **mark live** via mobile/tablet.

### 📦 Exam System Modules
| Feature | PDF Alignment | Platform Logic |
|--------|---------------|----------------|
| QCM | Matches 10-question written quiz | Auto-grading, eliminatory logic, timing |
| Socle 1 & 2 | Pre/post driving checks | Score inputs for each item (0/1) |
| Theme | Based on 6 themes from PDF | Oral evaluation, scored 0–3 |
| IO | Random oral questions | Marked 0–3 or E (Eliminatory) |
| Manoeuvres | M1 & M2 exercises | BON/ECHEC logic |
| Bilan | Final assessment & total | System computes results, flags failures |

### 📚 Learning Module
- Materials (PDF, Video, Audio, Résumé) linked to exam sections (e.g., Socle 1, Theme).
- Progress tracking (`completed`, `viewed_once`, etc.).
- Tailored learning paths based on PDF structure.

---

## ✅ Mapping PDF to Web System

| PDF Content | Database Tables (roadmap.md) |
|-------------|-------------------------------|
| Exam structure | `exam_sections`, `exam_items` |
| Scoring rules | `exam_results`, `exams` |
| QCM questions | `qcm_questions`, `qcm_answers`, `candidate_qcm_attempts` |
| Themes & IO | `oral_test_themes`, `exam_items` |
| Materials | `courses`, `course_materials`, `candidate_course_materials_progress` |

---

## ⚠️ Key Technical Challenges
- Handling **eliminatory logic** (both QCM and oral parts).
- **Live inspector marking** (mobile-first, real-time saves).
- Structuring **learning materials** to match official test content precisely.

---

## 📌 Conclusion

The platform's roadmap is well-aligned with the official exam structure. By digitizing the Fiche de Recueil and supporting learning through categorized materials, the system will improve exam preparation, simplify marking, and ensure compliance with Category C exam regulations.

