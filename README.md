# RepositoryObject Plugin - AttendanceList

Virtual attendance lists as repository objects in ILIAS. Some of the features:
* automatically generate, delete and schedule lists
* configure a minimum attendance and automatically calculate the progress
* ILIAS learning progress supported

## Requirements

| Component | Version(s)                                                                                    | Link                      |
|-----------|-----------------------------------------------------------------------------------------------|---------------------------|
| PHP       | ![](https://img.shields.io/badge/8.1-blue.svg) ![](https://img.shields.io/badge/8.2-blue.svg) | [PHP](https://php.net)    |
| ILIAS     | ![](https://img.shields.io/badge/9.x-orange.svg)                                              | [ILIAS](https://ilias.de) |

---

## Table of contents

<!-- TOC -->
* [RepositoryObject Plugin - AttendanceList](#repositoryobject-plugin---attendancelist)
  * [Requirements](#requirements)
  * [Table of contents](#table-of-contents)
  * [Installation](#installation)
      * [AttendanceList](#attendancelist)
<!-- TOC -->

---

## Installation

1. Clone this repository to **Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList**
2. Install the Composer dependencies
   ```bash
   cd Customizing/global/plugins/Services/Repository/RepositoryObject/AttendanceList
   composer install --no-dev
   ```
   Developers **MUST** omit the `--no-dev` argument.

3. Login to ILIAS with an administrator account (e.g. root)
4. Select **Plugins** in **Extending ILIAS** inside the **Administration** main menu.
5. Search for the **AttendanceList** plugin in the list of plugin and choose **Install** from the **Actions** drop-down.
6. Choose **Activate** from the **Actions** dropdown.

## Usage

1. Create or Enter a **Course**.
2. Click on the **Add New Item** Dropdown.
3. Select ``Attendance List`` under **Other**
4. Configure the new object.
5. Click on the button **Create attendance list**

### Cronjob

The plugin adds a cronjob that regularily sends out reminders to users to give a reason for their absence.

1. Go to **Administration** => **System Settings and Maintenance** => **General Settings** => Tab **Cron Jobs** => Search for ``AttendanceList: Send absence reminders``.
2. Activate the CronJob
3. Configure it by clicking on **Edit**
4. Define when the cronjob should run