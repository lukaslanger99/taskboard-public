*** Settings ***
Library    SeleniumLibrary

*** Keywords ***
Start Testcase
    Open Browser               http://lukaslanger.bplaced.net/taskboard/    chrome
    Maximize Browser Window

Finish Testcase
    Close Browser