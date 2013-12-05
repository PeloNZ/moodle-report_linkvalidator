This report scans a course and its activities for web links, and checks their validity.
Note: Alpha version.

The current features:
1) Check all activities in a course for links, at the top level, ie. anything that is in the activities "Edit Settings" page.
2) Display or download the report.

To do:
1) Category level reporting.
2) Scanning deeper within activities, eg within Forum posts, book pages etc.
3) Paginate the report (eg 100 lines per page, like the course log report).
4) Select whether to scan external/internal/both links.
5) php docs

Known issues:
1) The report is slow, as the checks connect to each link, and waiting for the http response code. I'll need to add timeouts, caching and pagination to fix this.
