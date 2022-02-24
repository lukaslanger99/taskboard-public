# SQL-Tables

## comments
Column name | Description
------------ | -------------
commentID | ID automatically initialized
commentTaskID | ID of task where it belongs to
commentType | task or subtask
commentAutor | userID of creator
commentDescription | text
commentDate | automatic timestamp eg. 2020-09-18

## groupaccess
Column name | Description
------------ | -------------
ID | ID automatically initialized
groupID | ID of group
userID | ID of user

## groups
Column name | Description
------------ | -------------
groupID | ID automatically initialized
groupName | name od Group
groupOwner | userID of group owner
groupPriority | priority (int) 1-999
groupInvites | enabled, disabled

## messages
Column name | Description
------------ | -------------
messageID | ID automatically initialized
messageOwner | userID of autor
messageGroup | groupID for motd, appointment
messageType | repeatingtask, motd, appointment, queue
messageTitle | title or name of message
messageWeekday | for reapeatingtask only: everyday,Mon,Tue,Wed,Thu,Fri,Sat,Sun
messageQuantity | for repeatingtask only: everyweek,odd,even
messageState | for repeatingtask only: last completion time stamp
messageDate | appointment: date of appointment, motd: date of creation
messagePrio | queue: 2 -> high, 1 -> normal

## panels
every user has his own entry
Column name | Description
------------ | -------------
userID | ID of user
panelRT | rt enabled true,false
panelMOTD | motd enabled true,false
panelAppointment | appointment enabled true,false
panelQueue | queue enabled true,false
panelWeather | weather enabled true, false
panelWeatherCity | selected city if weather panel enabled (will be saved if user disables and enables panel again), default berlin
panelTimetable | timetable enabled true, false

## tasks
Column name | Description
------------ | -------------
taskID | ID automatically initialized
taskType | task,subtask
taskParentID | groupID for task, taskID of parent for subtask
taskPriority | 1=low,2=normal,3=high
taskPriorityColor | red,#ffcc00,green
taskTitle | title
taskState | open,assigned,finished,archived
taskDateCreated | timestamp when created eg. 2021-03-05 12:34
taskDateAssigned | timestamp when assigned eg. 2021-03-05 12:34
taskAssignedBy | userID of assignee 
taskDateFinished | timestamp when finished eg. 2021-03-05 12:34
taskDescription | description

## timetableentrys
Column name | Description
------------ | -------------
timetableEntryID | ID automatically initialized
timetableID | ID of timetable
timetableText | activity text
timetableTimeStart | string format eg. 17:00 
timetableTimeEnd | same for ending time as timetableTimeStart
timetableWeekday | mon, tue, wed, thu, fri, sat, sun
timetableDate | date format: 2022-12-31
timetableOwnerID | userID of owner

## timetables
Column name | Description
------------|-------------
timetableID | ID auto increment
timetableUserID | userID of timetable owner
timetableWeek | week number eg. 07, 43
timetableYear | year eg. 2022

## tokens
Column name | Description
------------ | -------------
tokenID | ID automatically initialized
tokenType | joingroup(private link for single invite),resetpw,verifymail,groupinvite(public invite link)
tokenGroupID | joingroup: groupID
tokenUserID | userID of token owner
tokenToken | token itself 20 char long random string
tokenDate | timestamp when created eg.2021-03-23

## users
Column name | Description
------------ | -------------
userID | ID automatically initialized
userName | username
userType | normal,pro
userNameShort | shortname (3 letters)
userMail | email
userMailState | unverified,verified
usrePass | password (hashed)
userNightmode | enabled=true,disabled=false
userLastLogin | 0000-00-00 00:00:00
userLastMotd | 0000-00-00 00:00:00