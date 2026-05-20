# School ERP — Classroom Management System

## Overview

A web-based school ERP built with Laravel for managing classrooms, students,
teachers, marks, attendance, payments, and exam tools. Primarily serves a
religious/hawza school environment with Arabic RTL UI. Admins and teachers
manage everything through a Backpack CRUD admin panel; students access a
separate read-only portal to view their courses, marks, payments, and
certificates.

## Goals

1. Centralise student records — registration, personal info, academic history,
   financial support status
2. Track attendance, marks, and payments per student per course
3. Generate printable reports, transcripts, and certificates (PDF via mPDF)

## Core User Flow

### Admin / Teacher
1. Admin signs in to the Backpack admin panel (`/admin`)
2. Sets up AcademicPath → Curriculum → Course → ClassRoom
3. Enrolls students in ClassRooms; assigns teachers
4. Records attendance (AttendsCrudController) and marks
   (ClassRoomMarksController / StudentMarksCrudController)
5. Logs payments (PaymentCrudController)
6. Exports reports / PDFs from the Reports section

### Student
1. Student signs in at `/login` (custom `auth:student` guard)
2. Views dashboard: personal info, enrolled courses (current + past)
3. Expands each course card to see: timetable, payments, certificate/marks table

## Features

### Academic Structure
- AcademicPath (e.g. Hawza path types)
- Curriculum + CurriculumCategory (subjects with mark templates)
- Course (year + hijri year + semester + active flag)
- ClassRoom (links Course ↔ Curricula ↔ Students ↔ Teachers; stores
  `attend_table` and `teachers` as JSON columns)

### Student Management
- Full student profiles: CPR, DOB, mobile, address, financial state,
  hawza history, health history
- Computed `student_id` (year + zero-padded ID), age from CPR or DOB
- Soft-deletes; roles via Spatie Permission

### Attendance
- Attends model stores per-session attendance with a composite `code`
  (`date#time#classroomCode#curriculumId`)
- ClassRoom helper `getByDate()` resolves which sessions run on a given day;
  restricts to teacher's own classrooms when logged in as Teacher

### Marks
- StudentMarks per student per course, with a rich JSON `marks` column
- Curriculum defines `marks_labels` (templates for finalexam, midexam,
  project, practice, memorise, class, attend, etc.)
- `brief_marks` accessor composes structured mark objects for display

### Payments & Accounts
- Payment per student per course; tracks amount, source, type
- Payment print report via mPDF

### Exam Tools
- Upload exam files; background job packages them into a ZIP
- Students can download via signed URL

### Reports (PDF / Print)
- Student: attend list, attend report, scoring sheet, transcript, certificate,
  edu statement, courses transcript
- Course-level and institution-level stats
- Account / payment reports

### Student Portal
- Bootstrap 5 RTL, Arabic-first
- Login with CPR + password
- Dashboard shows current + past courses with expandable sections

### Roles & Permissions
- Spatie Permission via Backpack PermissionManager
- Built-in roles: SuperAdmin, Teacher (at minimum)
- Teacher role restricts classroom visibility to own classes

## Scope

### In Scope
- Admin CRUD for all entities via Backpack
- Student self-service portal (read-only)
- PDF report generation
- Arabic RTL support
- Docker deployment

### Out of Scope
- Public registration (students are created by admin)
- Payment gateway integration (payments recorded manually)
- Real-time notifications

## Success Criteria

1. Admin can create a Course → ClassRoom → enroll students, record marks, and
   generate a PDF certificate
2. Student can log in and view their marks and payment history
3. Attendance can be recorded per curriculum session per day
