<div align="center">
  <a href="https://hireflow.fun">
    <img src="https://hireflow.fun/Background.png" alt="HireFlow Project Banner" width="100%" height="300" style="object-fit: cover; border-radius: 12px;">
  </a>

  <br />
  
  <h1>HireFlow: AI-Powered Applicant Tracking & Ed-Tech Platform</h1>
  <p><b>Bridging the gap between Employability, Skill-building, and Modern Recruitment.</b></p>
  
  <p><b>Live Link: <a href="https://hireflow.fun">hireflow.fun</a></b></p>

  <p>
    <img src="https://img.shields.io/badge/Status-Active-success?style=for-the-badge" alt="Status" />
    <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License" />
  </p>
</div>

---

## Hackathon Details
* **Hackathon:** Seva Sankalp (Institute Level)
* **Team Name:** Runtime Monarch
* **Problem Sector:** Sector #2 - Education & Future Work (Employability, Skills, and Ed-Tech)

---

## Tech Stack
We built this platform using a robust, scalable, and modern stack designed for speed and reliability.

| Technology | Usage |
| :--- | :--- |
| ![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white) | Core Backend Logic & Routing |
| ![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white) | Frontend Styling (Glassmorphism & Dark Mode) |
| ![Supabase](https://img.shields.io/badge/Supabase-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white) | Secure Authentication API |
| ![PostgreSQL](https://img.shields.io/badge/postgresql-4169e1?style=for-the-badge&logo=postgresql&logoColor=white) | Relational Database Management |
| ![Hostinger](https://img.shields.io/badge/Hostinger-673AB7?style=for-the-badge&logo=hostinger&logoColor=white) | Live Production Hosting |
| ![Google Gemini](https://img.shields.io/badge/AI-Google_Gemini-blue?style=for-the-badge&logo=google) | AI Chatbot, Mock Tests & Roadmap Generation |

---

## How the ATS (Applicant Tracking System) Works
Unlike traditional platforms that blindly forward resumes, our built-in ATS engine pre-evaluates candidates to give them real-time feedback on their employability for a specific role.

1. **Client-Side Parsing:** Using `pdf.js`, the platform securely extracts text from the candidate's PDF resume directly in the browser (ensuring data privacy and zero server latency).
2. **Sanitization & Extraction:** Both the Job Description (JD) and the Resume are converted to lowercase, stripped of special characters, and filtered to remove filler words (words under 3 letters).
3. **Keyword Deduplication:** The engine maps unique required skills from the JD into a Set.
4. **Scoring Algorithm:** 
   * It cross-references the resume's vocabulary against the JD's required keywords.
   * Every applicant gets a baseline score of **40 points**.
   * The remaining **60 points** are awarded based on the exact match ratio of unique keywords.
   * The final score determines the UI verdict: **Great Match (75-99)**, **Good Potential (50-74)**, or **Needs Optimization (<50)**.

---

## Candidate User Flow
The platform is designed to not just find jobs, but to prepare candidates for them.

1. **Onboarding:** Candidate signs up and builds their profile.
2. **Discovery:** Browse active job postings tailored to their location and job type (Remote/On-site).
3. **AI Preparation Hub (The Ed-Tech Edge):**
   * **AI Recruiter Chat:** Candidates can ask an AI bot questions about the specific Job Description.
   * **Custom Roadmaps:** The AI instantly generates a 4-step preparation guide on how to secure the role.
   * **Mock Tests:** Candidates can take AI-generated Easy, Medium, or Hard multiple-choice quizzes based strictly on the skills required for the job.
4. **Application:** The candidate uploads their resume, receives their ATS score, and submits their application to the recruiter.

---

## The Recruiter Side
A seamless, glassmorphic dashboard built for HR professionals to manage their pipeline.

1. **Company Registration:** Recruiters can either register a brand-new company or join an existing pipeline using a secure, unique `join_code`.
2. **Job Management:** Recruiters can easily post new roles, defining requirements, location, pay, and job types.
3. **Applicant Tracking Dashboard:** 
   * View all active postings at a glance.
   * Review incoming candidates ranked dynamically by their **ATS Match Score**.
   * Update application statuses (`Applied`, `Interviewing`, `Offered`, `Rejected`) to keep the pipeline moving.

---

## Database Schema
Our PostgreSQL database is normalized and strictly relational to ensure data integrity across companies, recruiters, candidates, and AI-generated content.

```sql
-- Core Entities
CREATE TABLE public.companies (
  company_id uuid NOT NULL DEFAULT gen_random_uuid(),
  name text NOT NULL,
  description text,
  logo_url text,
  join_code character varying NOT NULL UNIQUE,
  website_url text,
  socials jsonb DEFAULT '{"twitter": "", "facebook": "", "linkedin": "", "instagram": ""}'::jsonb,
  created_at timestamp with time zone DEFAULT now(),
  CONSTRAINT companies_pkey PRIMARY KEY (company_id)
);

CREATE TABLE public.recruiters (
  recruiter_id uuid NOT NULL DEFAULT gen_random_uuid(),
  user_id uuid UNIQUE,
  name text NOT NULL,
  email text NOT NULL UNIQUE,
  company_id uuid,
  created_at timestamp with time zone DEFAULT now(),
  last_active timestamp with time zone DEFAULT now(),
  CONSTRAINT recruiters_pkey PRIMARY KEY (recruiter_id),
  CONSTRAINT recruiters_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id),
  CONSTRAINT recruiters_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(company_id)
);

CREATE TABLE public.candidates (
  candidate_id uuid NOT NULL DEFAULT gen_random_uuid(),
  user_id uuid UNIQUE,
  name text NOT NULL,
  email text NOT NULL UNIQUE,
  resume_url text,
  skills ARRAY,
  created_at timestamp with time zone DEFAULT now(),
  first_name text,
  last_name text,
  country_code text,
  phone_number text,
  city text,
  state text,
  CONSTRAINT candidates_pkey PRIMARY KEY (candidate_id),
  CONSTRAINT candidates_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id)
);

-- Job & Application Entities
CREATE TABLE public.jobs (
  job_id uuid NOT NULL DEFAULT gen_random_uuid(),
  company_id uuid,
  recruiter_id uuid,
  role text NOT NULL,
  job_description text NOT NULL,
  job_type text CHECK (job_type = ANY (ARRAY['full-time'::text, 'internship'::text, 'part-time'::text, 'contract'::text, 'freelance'::text])),
  pay_amount numeric,
  currency character varying DEFAULT 'USD'::character varying,
  is_remote boolean DEFAULT false,
  city text,
  country text,
  created_at timestamp with time zone DEFAULT now(),
  status text DEFAULT 'Open'::text,
  CONSTRAINT jobs_pkey PRIMARY KEY (job_id),
  CONSTRAINT jobs_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(company_id),
  CONSTRAINT jobs_recruiter_id_fkey FOREIGN KEY (recruiter_id) REFERENCES public.recruiters(recruiter_id)
);

CREATE TABLE public.applications (
  application_id uuid NOT NULL DEFAULT gen_random_uuid(),
  candidate_id uuid,
  job_id uuid,
  status text DEFAULT 'Applied'::text CHECK (status = ANY (ARRAY['Applied'::text, 'Interviewing'::text, 'Offered'::text, 'Rejected'::text])),
  current_round integer DEFAULT 1,
  feedback text,
  applied_at timestamp with time zone DEFAULT now(),
  ats_score numeric DEFAULT 0,
  resume_url text,
  CONSTRAINT applications_pkey PRIMARY KEY (application_id),
  CONSTRAINT applications_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(job_id)
);

-- AI Prep Entities
CREATE TABLE public.job_roadmaps (
  roadmap_id uuid NOT NULL DEFAULT gen_random_uuid(),
  job_id uuid NOT NULL,
  step_number integer NOT NULL,
  header text NOT NULL,
  description text,
  created_at timestamp with time zone DEFAULT now(),
  CONSTRAINT job_roadmaps_pkey PRIMARY KEY (roadmap_id),
  CONSTRAINT job_roadmaps_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(job_id)
);

CREATE TABLE public.job_mock_questions (
  question_id uuid NOT NULL DEFAULT gen_random_uuid(),
  job_id uuid NOT NULL,
  difficulty text DEFAULT 'Medium'::text CHECK (difficulty = ANY (ARRAY['Easy'::text, 'Medium'::text, 'Hard'::text])),
  question_text text NOT NULL,
  option_a text NOT NULL,
  option_b text NOT NULL,
  option_c text NOT NULL,
  option_d text NOT NULL,
  correct_option character CHECK (correct_option = ANY (ARRAY['A'::bpchar, 'B'::bpchar, 'C'::bpchar, 'D'::bpchar])),
  created_at timestamp with time zone DEFAULT now(),
  CONSTRAINT job_mock_questions_pkey PRIMARY KEY (question_id),
  CONSTRAINT job_mock_questions_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(job_id)
);
