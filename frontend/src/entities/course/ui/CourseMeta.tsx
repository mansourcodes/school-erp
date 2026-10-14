import {
    BookMarked,
    Calendar,
    CalendarCheck,
    Hourglass,
    LayoutGrid,
    type LucideIcon,
    MapPin,
} from "lucide-react";
import type { ReactNode } from "react";
import { t } from "@/shared/i18n";
import { fmtDate, fmtHijri } from "@/shared/lib";
import { ArchPhoto } from "@/shared/ui";
import type { CourseDetail } from "../model/types";

function Row({
    icon: Icon,
    label,
    children,
    wide,
}: {
    icon: LucideIcon;
    label: string;
    children: ReactNode;
    wide?: boolean;
}) {
    return (
        <div className={`flex gap-3 items-start ${wide ? "col-span-2" : ""}`}>
            <Icon
                className="size-4 mt-1.5 text-primary shrink-0"
                strokeWidth={1.75}
                aria-hidden="true"
            />
            <div>
                <dt className="text-[0.8125rem] opacity-60">{label}</dt>
                <dd className="font-medium">{children}</dd>
            </div>
        </div>
    );
}

function DateValue({ iso }: { iso: string | null }) {
    if (!iso) return <>—</>;
    return (
        <>
            {fmtDate(iso)}
            <span className="text-xs opacity-60 block">{fmtHijri(iso)}</span>
        </>
    );
}

interface CourseTeacher {
    name: string;
    photoUrl: string | null;
    subjects: string[];
}

/** One entry per teacher name, with their subjects; the first photo found for that name wins. */
function courseTeachers(course: CourseDetail): CourseTeacher[] {
    const byName = new Map<string, CourseTeacher>();
    for (const curriculum of course.curricula) {
        const name = curriculum.teacher?.trim();
        if (!name) continue;
        const entry = byName.get(name) ?? {
            name,
            photoUrl: null,
            subjects: [],
        };
        entry.photoUrl ??= curriculum.teacher_photo_url;
        if (curriculum.name) entry.subjects.push(curriculum.name);
        byName.set(name, entry);
    }
    return [...byName.values()];
}

function Teachers({ teachers }: { teachers: CourseTeacher[] }) {
    return (
        <section aria-labelledby="course-teachers" className="mb-5">
            <h4
                id="course-teachers"
                className="text-[0.8125rem] opacity-60 mb-2"
            >
                {t("course.teachers")}
            </h4>
            <ul className="grid sm:grid-cols-2 gap-3">
                {teachers.map((teacher) => (
                    <li
                        key={teacher.name}
                        className="flex items-center gap-3 rounded-field bg-base-100 sm:bg-base-200/60 p-3"
                    >
                        <ArchPhoto
                            className="w-16 h-20"
                            textClassName="text-2xl"
                            src={teacher.photoUrl}
                            alt={teacher.name}
                            initial={Array.from(teacher.name)[0] ?? ""}
                        />
                        <div className="min-w-0">
                            <p className="font-semibold leading-snug">
                                {teacher.name}
                            </p>
                            {teacher.subjects.length > 0 && (
                                <p className="text-sm opacity-70">
                                    {teacher.subjects.join("، ")}
                                </p>
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </section>
    );
}

/** Same facts as the admin student timetable (`student_table_template`), led by the course's teachers. */
export function CourseMeta({ course }: { course: CourseDetail }) {
    const teachers = courseTeachers(course);

    return (
        <>
            {teachers.length > 0 && <Teachers teachers={teachers} />}
            <dl className="grid grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4">
                <Row icon={LayoutGrid} label={t("course.roomNumber")}>
                    <span className="num">
                        {course.class_room_number ?? "—"}
                    </span>
                </Row>
                <Row icon={BookMarked} label={t("course.path")}>
                    {course.academic_path ?? "—"}
                </Row>
                <Row icon={MapPin} label={t("course.place")} wide>
                    {course.class_room_name ?? "—"}
                </Row>
                <Row icon={Calendar} label={t("course.start")}>
                    <DateValue iso={course.start_date} />
                </Row>
                <Row icon={CalendarCheck} label={t("course.end")}>
                    <DateValue iso={course.end_date} />
                </Row>
                <Row icon={Hourglass} label={t("course.hours")}>
                    {/* Usually a number of hours, but the admin field is free text. */}
                    {course.duration === null
                        ? "—"
                        : /^\d+$/.test(String(course.duration))
                          ? t("course.hoursValue", { n: course.duration })
                          : course.duration}
                </Row>
            </dl>
        </>
    );
}
