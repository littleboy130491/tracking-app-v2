Admin dashboard:

- CRUD users
- CRUD customers / companies
- CRUD bill of ladings
- CRUD containers (can be done inside bill of ladings or by its own model)
- CRUD workflows, this will determine the step by step standard process with fields that can be operated.
- view only logs (auto created information that will monitor change for users, customers, bill of ladings, containers), it records activity, date, who triggers it

relationship:

- user can handle multiple customers
- customer can be handled by multiple user
- bill of lading belong to a customer / company
- customer can have multiple bill of lading
- bill of lading can have multiple containers
- container belong to a bill of lading

roles:

- regular user, visit customer dashboard only, check their company bill of ladings and containers
- operators user, visit admin dashboard, update fields that belong to their authority (customizable)
- admin user, manage everything

Customer dashboard:

- no register feature
- login using their registered email address
- passwordless, OTP will be sent to their email address. in production this will be sent using SMTP mailgun, in development user might change the .env to put the OTP directly to the login page.
- there is a greeting for their name
- a header with filter, where they can filter the bill of ladings based on number, status, year, month.
- a list of their bill of ladings that belong to the companies they manage (relationship through company)
- click the bill of ladings and they will visit the bill of ladings detail page
    - can confirm if type import, confirmed_by_customer field
- inside bill of ladings detail page:
    - a list of containers belong to this bill of ladings, when clicked, it will open new tab to container detail page

fields bill of ladings:

-
