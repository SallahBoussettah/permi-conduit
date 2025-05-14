# Roadmap: Laravel Driving Exam Platform

This document outlines the development roadmap for a Laravel-based web application to manage Category C driving exams, candidate progression, and inspector workflows, based on the provided PDF (`CategoryC.pdf`), QCM specifications, and Plateau structure details.

## Core Requirements:

* **User Roles:**
    * **Candidate:** Can sign in, access their results, watch/access course materials (PDFs, Videos, Audio, Résumés) tailored to exam sections, and take QCM exams.
    * **Inspector:** Can sign in, manage exam sessions, manage courses, and input candidate results live during the exam.
* **Exam Process:** Digitize the "Épreuve Hors Circulation Catégorie C" (Plateau) as detailed in official documents, including QCM, Socle 1 & 2 checks, Themed questions, Oral Interrogation, and Maneuvers.
* **Live Marking:** Inspectors will mark results directly in the system while with the candidate, often using mobile devices.

## Phase 1: Project Setup & Foundations

1.  **Laravel Project Initialization:**
    * Install latest stable Laravel.
    * Configure `.env` (database, app URL, mail, etc.).
    * **Choice:** Consider using Laravel Sail for a Docker-based local development environment for consistency.
2.  **Database Design (Initial ERD):**
    * `users` (id, name, email, password, role_id)
    * `roles` (id, name - e.g., 'candidate', 'inspector', 'admin')
    * `exam_types` (id, name - e.g., "Catégorie C - Épreuve Hors Circulation", pdf_reference)
    * `exam_sections` (id, exam_type_id, name - e.g., "Interrogation Écrite (QCM)", "Socle Minimum 1", "Thème", "Interrogation Orale", "Socle Minimum 2", "Manoeuvre"), `official_max_points` (integer, nullable - e.g., 7 for Socle 1, as per Plateau breakdown image), `sequence_order` (integer)
    * `courses` (id, title, description, `exam_section_id` (nullable FK - to link a general course to an exam section))
    * `course_materials` (id, `course_id` (FK), title, `material_type` (enum: 'pdf', 'video', 'audio', 'text', 'resume', 'external_link'), `content_path_or_url`, description, sequence_order)
    * `candidate_course_materials_progress` (user_id, course_material_id, status (e.g., 'not_started', 'in_progress', 'completed', 'viewed_once'), `last_accessed_at` (timestamp), `completion_percentage` (integer, for videos/longer texts))
    * `exams` (id, candidate_id, inspector_id, exam_type_id, exam_date, status - e.g., pending_qcm, qcm_taken, practical_scheduled, in_progress, completed, location_details, qcm_passed_at (nullable timestamp), `qcm_score_correct_answers` (integer, nullable), `qcm_notation` (integer, nullable - stores 3, 2, 1, or 0 based on QCM rules), `qcm_is_eliminatory` (boolean, default: false, nullable))
    * `exam_items` (id, `exam_section_id` (FK), description - from PDF, `scoring_type` - e.g., points_0_1, points_E_0_1_2_3, bon_echec, `reference_in_pdf` (e.g., page number or item code))
    * `exam_results` (id, `exam_id` (FK), `exam_item_id` (FK), score_achieved, notes_by_inspector)
    * `qcm_questions` (id, `qcm_set_id` (nullable FK, if grouping QCMs), question_text, `exam_section_id` (FK, linking to "Interrogation Écrite" section))
    * `qcm_answers` (id, `qcm_question_id` (FK), answer_text, is_correct)
    * `candidate_qcm_attempts` (id, `exam_id` (FK), `qcm_question_id` (FK), `selected_qcm_answer_id` (nullable FK), is_correct_at_submission)
    * `oral_test_themes` (id, name - e.g., "Documents de bord, triangle, extincteur")
    * `notifications` (id, user_id, message, read_at, link) (Optional, for exam scheduling etc.)
3.  **Authentication & Authorization:**
    * **Choice:** Implement Laravel Breeze (Blade + simple auth) or Laravel Jetstream (Livewire/Inertia + teams, profile management). **Recommendation:** Start with Breeze for simplicity.
    * Set up middleware for role-based access control.
4.  **Basic UI/UX Structure:**
    * **Choice:** Blade + Alpine.js + Tailwind CSS 3.3.3 for rapid development.
    * Create basic layouts for public, candidate, and inspector dashboards.

## Phase 2: User Role Implementation

1.  **Admin Area (Optional but Recommended):**
    * CRUD for users, roles, courses, course_materials, exam_types, exam_sections, exam_items, qcm_questions.
    * **Choice:** Filament or Laravel Nova. **Recommendation:** Filament.
2.  **Inspector Dashboard & Profile:**
    * View assigned exams. Manage schedule. Tools for managing exam sessions.
3.  **Candidate Dashboard & Profile:**
    * View personal information. Links to courses (showing material types), QCM status, and exam schedule.

## Phase 3: Course Module

1.  **Inspector - Course & Material Management:**
    * CRUD for `courses` (e.g., "Préparation Socle 1", "Thèmes Oraux").
    * Within each course, CRUD for `course_materials` allowing upload/linking of PDFs, videos, audio files, text content, or résumés.
    * Ability to associate courses and materials with specific `exam_sections` to guide candidate study.
2.  **Candidate - Course Access & Learning:**
    * **Course Listing and Navigation:**
        * Candidates will see a clear, organized list of all courses they are enrolled in or that are available for their exam type.
        * Courses will be visually grouped or filterable by the `exam_section` they pertain to (e.g., "Interrogation Écrite (QCM) Preparation", "Socle Minimum 1 Study Materials", "Thèmes Oraux - Audio Guides").
        * Each course listing should indicate the types of materials it contains (e.g., icons for PDF, video, audio).
        * Display overall progress for a course if it contains multiple materials (e.g., "3 out of 5 materials completed").
    * **Accessing Diverse Learning Materials:**
        * **PDFs:** Viewable directly in the browser (e.g., using a JS PDF viewer like PDF.js or via an embedded iframe) or downloadable.
        * **Videos:** Embeddable player (e.g., HTML5 `<video>` tag for self-hosted, or embedded YouTube/Vimeo links). Consider tracking video watch percentage.
        * **Audio:** Embeddable player (e.g., HTML5 `<audio>` tag).
        * **Text/Résumé:** Displayed as formatted HTML content within a dedicated page.
        * **External Links:** Clearly marked and open in a new tab.
        * Each material will have its own page or modal view, with its title and description.
        * Navigation within a course (e.g., "Next Material," "Previous Material") should be intuitive.
    * **Tracking Progress (`candidate_course_materials_progress` table):**
        * When a candidate interacts with a material (e.g., opens a PDF, starts a video, views a text page), its status in `candidate_course_materials_progress` is updated.
        * **`status` field:**
            * `not_started`: Default.
            * `in_progress`: For materials like videos where partial completion is possible.
            * `completed`: Manually marked by the candidate (e.g., a "Mark as Complete" button) or automatically (e.g., video watched to 90%, PDF scrolled to end).
            * `viewed_once`: Simpler tracking if detailed progress isn't needed for certain material types.
        * **`last_accessed_at`:** Automatically updated on access.
        * **`completion_percentage`:** (Optional, for videos or long text materials) Store how much of the content has been consumed.
        * Visual indicators of progress (e.g., checkmarks next to completed materials, progress bars for courses).
        * The system should remember where a candidate left off in a video or long text if feasible.
    * **Prerequisites/Sequencing (Optional):**
        * Consider if some courses or materials should only become available after completing others. This would require additional logic and database fields (e.g., `prerequisite_material_id`).


All candidates follow a structured learning path under the "Plateau" section, which includes:

| # | Module      | Content Type             | Points |
|---|-------------|--------------------------|--------|
| 1 | QCM (IE)    | QCM                      | 3 pts  |
| 2 | Socle 1     | Video / PDF              | 7 pts  |
| 3 | Thème       | Video / PDF              | 3 pts  |
| 4 | I.O.        | Audio / PDF / Summary    | 3 pts  |
| 5 | Socle 2     | Video / PDF              | 4 pts  |
| 6 | M.A.        | Video                    | 1 pt   |


## Phase 4: Exam Module - "Interrogation Écrite" (QCM)

1.  **Inspector - QCM Management:**
    * CRUD for QCM questions (10 per set for this exam type) and answers, linked to the "Interrogation Écrite" `exam_section`.
2.  **Candidate - QCM Taking Interface:**
    * **Exam Conditions:** 10 questions, 6-minute maximum timer.
    * **Submission & Auto-Grading:** Auto-grades on submission or timer expiry.
    * **Scoring Logic:**
        * 9-10 correct: Notation 3
        * 7-8 correct: Notation 2
        * 6 correct: Notation 1
        * 5 correct: Notation 0
        * <5 correct: Éliminatoire
    * **Results Storage:** Correct answers count, notation, and eliminatory status stored in `exams` table.
    * **Result Display:** Overall QCM status displayed later on dashboard (not detailed correction immediately).
    * **Workflow Note:** Eliminatory QCM prevents practical exam scheduling. Successful QCM is prerequisite.

## Phase 5: Exam Module - Practical Exam (Plateau) & Live Marking

1.  **Data Seeding for Exam Structure:**
    * Populate `exam_sections` with names (QCM, Socle 1, Theme, IO, Socle 2, Manoeuvre) and `official_max_points` based on Plateau breakdown (QCM: 3, Socle 1: 7, Theme: 3, IO: 3, Socle 2: 4).
    * Populate `exam_items` for each section based on the detailed `Fiche de Recueil` from `CategoryC.pdf`.
2.  **Inspector - Exam Session Management:**
    * Create exam sessions, assign candidates.
    * **Scheduling:** Set practical exam dates (contingent on non-eliminatory QCM).
3.  **Inspector - Live Marking Interface:**
    * **Mobile-First Design:** Optimized for phones/tablets.
    * Mirrors "FICHE DE RECUEIL" (PDF page 3).
    * **Marking `exam_items` within each `exam_section`:**
        * **Socle 1 (target 7 pts), Theme (target 3 pts), IO (target 3 pts), Socle 2 (target 4 pts):** Score individual `exam_items` (0/1, E/0/1/2/3 as per PDF).
        * Other sections as per PDF (Immobilisation, Documents, Cabine, Moteur, Freinage, Gestes, Manoeuvres).
    * **Calculations:**
        * System calculates "Total général" for knowledge/checks based on summed `exam_results` for relevant items. This total is compared against the >16 points threshold.
        * The `official_max_points` for sections can be displayed for inspector reference.
        * Implement logic for "BON si total > à 16 Pts".
        * Implement logic for "Bilan partiel" and "Bilan final" (INSUFFISANT/FAVORABLE) considering total points, no eliminatory marks (practical 'E' or QCM eliminatory), and favorable maneuver.
    * Input fields, observations, abandonment checkbox.
    * Real-time saving. **Choice:** Livewire for dynamic UX.

## Phase 6: Results & Reporting

1.  **Candidate - View Results:**
    * Detailed breakdown: QCM notation, practical exam scores per item/section, total points, final outcome. Inspector's observations.
2.  **Inspector - View Past Exams & Candidate History:**
    * Access records, including QCM performance and detailed practical results.
3.  **Admin - Reporting (Optional):**
    * Overall pass/fail rates, performance by section/item.

## Phase 7: Testing, Refinement & Deployment

1.  **Testing:** Unit, Feature, Manual (especially mobile marking & QCM), UAT.
2.  **Refinement:** Bug fixes, usability improvements, performance optimization.
3.  **Deployment:** **Choice:** Laravel Forge, Ploi.io, Vapor. **Recommendation:** Forge. CI/CD.

## Key Considerations from PDF, QCM Rules & Plateau Structure:

* **Eliminatory Marks:** System must handle 'E' marks from practical items and "Éliminatoire" status from QCM.
* **Scoring Logic:** Adhere to QCM notation rules and practical exam item scoring, leading to the overall "Total général" and >16 points threshold.
* **Learning Material Integration:** Courses and materials (PDF, Video, Audio) should be clearly linked to the exam sections they prepare for (Socle 1, Themes, IO, etc.).

## Technology Choices Summary:

* **Backend:** Laravel
* **Frontend (Recommended):** Blade, Tailwind CSS 3.3.3, Alpine.js
* **Database:** MySQL or PostgreSQL
* **Authentication (Recommended):** Laravel Breeze
* **Admin Panel (Optional Recommendation):** Filament
* **Live Marking UI (Optional Recommendation):** Livewire

This roadmap provides a structured approach. Each phase can be broken down further into smaller tasks. Remember to iterate and get feedback, especially from potential inspector users.
