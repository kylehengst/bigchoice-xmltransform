DROP TABLE IF EXISTS `Jobs_Categories`;
DROP TABLE IF EXISTS `Jobs_Regions`;
DROP TABLE IF EXISTS `Jobs_WorkTypes`;
DROP TABLE IF EXISTS `Jobs`;
DROP TABLE IF EXISTS `Companies`;
DROP TABLE IF EXISTS `Categories`;
DROP TABLE IF EXISTS `Regions`;
DROP TABLE IF EXISTS `WorkTypes`;

CREATE TABLE `Jobs` (
    `JobID` INTEGER PRIMARY KEY AUTOINCREMENT,
    `CompanyRef` TEXT,
    `JobTitle` TEXT,
    `SummaryLocation` TEXT,
    `SalaryBenefits` TEXT,
    `Summary` TEXT,
    `Description` TEXT,
    `JobType` TEXT,
    `ApplicationURL` TEXT
);

CREATE TABLE `Jobs_Categories` (
    `JobID` INTEGER,
    `CategoryID` INTEGER,
    FOREIGN KEY(JobID) REFERENCES Jobs(JobID),
    FOREIGN KEY(CategoryID) REFERENCES Categories(CategoryID)
);

CREATE TABLE `Jobs_Regions` (
    `JobID` INTEGER,
    `RegionID` INTEGER,
    FOREIGN KEY(JobID) REFERENCES Jobs(JobID),
    FOREIGN KEY(RegionID) REFERENCES Regions(RegionID)
);

CREATE TABLE `Jobs_WorkTypes` (
    `JobID` INTEGER,
    `WorkTypeID` INTEGER,
    FOREIGN KEY(JobID) REFERENCES Jobs(JobID),
    FOREIGN KEY(WorkTypeID) REFERENCES WorkTypes(WorkTypeID)
);

CREATE TABLE `Companies` (
    `CompanyID` INTEGER PRIMARY KEY AUTOINCREMENT,
    `CompanyRef` TEXT,
    `CompanyName` TEXT,
    `ProfileType` TEXT,
    `ProfileText` TEXT
);

CREATE TABLE `Categories` (
    `CategoryID` INTEGER PRIMARY KEY AUTOINCREMENT,
    `CategoryName` TEXT
);

CREATE TABLE `Regions` (
    `RegionID` INTEGER PRIMARY KEY AUTOINCREMENT,
    `RegionName` TEXT
);

CREATE TABLE `WorkTypes` (
    `WorkTypeID` INTEGER PRIMARY KEY AUTOINCREMENT,
    `WorkTypeName` TEXT
);