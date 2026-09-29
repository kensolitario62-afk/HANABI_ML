-- =====================================================================

-- Enrollment flow update for database `hipanao_db`
--
-- RUN THIS ONCE. phpMyAdmin > click hipanao_db > SQL tab > paste > Go.
-- It does NOT drop anything. Existing enrollment rows are kept and
-- backfilled so they stay valid under the new two-stage flow.
--
-- WHAT IT CHANGES AND WHY
-- The old tblenrollment forced section and enrollment date at the very
-- moment the record was created, so reserving a slot and actually being
-- enrolled were the same event. Your reference layout separates them:
-- DATE_RESERVED comes first, SECTIONING and DATE_ENROLLED come later.
--
-- New columns             Reference column it matches
--   CATEGORY                CATEGORY
--   CURRICULUM_YR           CURRICULUM_YR
--   DATE_RESERVED           DATE_RESERVED
--   ENCODED_BY              USERNAME  (kept as a real FK to tblusers
--                                      instead of a loose name string)
--
-- Already covered by existing columns
--   SY_ID       = AY          (via tblschoolyear.SCHOOL_YEAR)
--   SECTION_ID  = SECTIONING  (via tblsections, as a real FK)
--   S_ID        = IDNO        (via tblstudent, as a real FK)
--   SEMESTER, STATUS, DATE_ENROLLED, COURSE_ID unchanged in meaning
--
-- NOT added: DEPT_ID. There is no department table in this database,
-- so the column would sit empty with nothing behind it. Course already
-- identifies the program. Say the word if you want a tbldepartment and
-- this column wired to it properly.
-- =====================================================================

USE `hipanao_db`;

SET FOREIGN_KEY_CHECKS = 0;


-- ---------------------------------------------------------------------
-- 1. Clean up anything that would block the column changes below
-- ---------------------------------------------------------------------
UPDATE `tblenrollment` SET `SEMESTER` = '1st Semester' WHERE `SEMESTER` IS NULL OR `SEMESTER` = '';
UPDATE `tblenrollment` SET `STATUS`   = 'Enrolled'     WHERE `STATUS`   IS NULL OR `STATUS`   = '';


-- ---------------------------------------------------------------------
-- 2. Drop the section foreign key FIRST.
--    MySQL will not let you change the definition of a column that a
--    foreign key is sitting on, so this has to come before step 4.
-- ---------------------------------------------------------------------
ALTER TABLE `tblenrollment` DROP FOREIGN KEY `fk_enrollment_section`;


-- ---------------------------------------------------------------------
-- 3. Add the reservation-stage columns
-- ---------------------------------------------------------------------
ALTER TABLE `tblenrollment`
  ADD COLUMN `CATEGORY`      varchar(30) NOT NULL DEFAULT 'New' AFTER `SEMESTER`,
  ADD COLUMN `CURRICULUM_YR` varchar(20) DEFAULT NULL           AFTER `CATEGORY`,
  ADD COLUMN `DATE_RESERVED` date        DEFAULT NULL           AFTER `CURRICULUM_YR`,
  ADD COLUMN `ENCODED_BY`    int(11)     DEFAULT NULL           AFTER `STATUS`;


-- ---------------------------------------------------------------------
-- 4. Loosen the two columns that belong to the SECOND stage
--    A reserved student has no section and no enrollment date yet, so
--    neither one can stay NOT NULL.
-- ---------------------------------------------------------------------
ALTER TABLE `tblenrollment`
  MODIFY COLUMN `SECTION_ID`    int(11)     DEFAULT NULL,
  MODIFY COLUMN `DATE_ENROLLED` date        DEFAULT NULL,
  MODIFY COLUMN `SEMESTER`      varchar(20) NOT NULL,
  MODIFY COLUMN `STATUS`        varchar(30) NOT NULL DEFAULT 'Reserved';


-- ---------------------------------------------------------------------
-- 5. Backfill the rows that already exist
--    They were created as fully enrolled, so treat their enrollment
--    date as the reservation date too.
-- ---------------------------------------------------------------------
UPDATE `tblenrollment`
   SET `DATE_RESERVED` = `DATE_ENROLLED`
 WHERE `DATE_RESERVED` IS NULL
   AND `DATE_ENROLLED` IS NOT NULL;

UPDATE `tblenrollment` e
  JOIN `tblschoolyear` sy ON sy.SY_ID = e.SY_ID
   SET e.`CURRICULUM_YR` = sy.`SCHOOL_YEAR`
 WHERE e.`CURRICULUM_YR` IS NULL;

UPDATE `tblenrollment` SET `CATEGORY` = 'New' WHERE `CATEGORY` = '' OR `CATEGORY` IS NULL;

-- Credit the existing rows to the admin account.
UPDATE `tblenrollment` SET `ENCODED_BY` = 1 WHERE `ENCODED_BY` IS NULL;


-- ---------------------------------------------------------------------
-- 6. One enrollment per student, per school year, per semester.
--    This is what stops the same student being reserved twice for the
--    same term, at the database level rather than only in PHP.
-- ---------------------------------------------------------------------
ALTER TABLE `tblenrollment`
  ADD UNIQUE KEY `uq_enrollment_term` (`S_ID`, `SY_ID`, `SEMESTER`);


-- ---------------------------------------------------------------------
-- 7. Put the section foreign key back, now allowing NULL, and add the
--    new one for ENCODED_BY.
--    No ADD KEY here: MySQL creates the supporting index automatically
--    when the constraint is added, and naming both the same would fail
--    with a duplicate key name.
-- ---------------------------------------------------------------------
ALTER TABLE `tblenrollment`
  ADD CONSTRAINT `fk_enrollment_section` FOREIGN KEY (`SECTION_ID`)
      REFERENCES `tblsections` (`SECTION_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tblenrollment`
  ADD CONSTRAINT `fk_enrollment_encodedby` FOREIGN KEY (`ENCODED_BY`)
      REFERENCES `tblusers` (`UID`) ON DELETE SET NULL ON UPDATE CASCADE;


SET FOREIGN_KEY_CHECKS = 1;


-- ---------------------------------------------------------------------
-- Check the result
-- ---------------------------------------------------------------------
-- DESC `tblenrollment`;
