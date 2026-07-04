# DSJV Website Architecture Overview

## High-Level Components

- **Client Browser**
  - Desktop and mobile users
  - Uses cookies, form submissions, and AJAX search requests
- **PHP Frontend Router**
  - `index.php` is the main landing page and routing entrypoint
  - Determines user type and redirects to feature modules
- **Feature Modules**
  - `new_panchang.php` â€” Panchang / daily astrology details
  - `Birth_input.php` â€” Birth chart input and history
  - `Matching_input.php` â€” Compatibility matching
  - `Jamakol_input.php` â€” Jamakol-specific input flows
  - `freeuser.php` â€” Default page for free/anonymous users
- **Shared Backend Services**
  - `db.php` / `db_config.php` â€” MySQL connection and helpers
  - `lib.php` â€” application constants, session helpers, translation helpers
  - `lang/lang.php` + language files â€” multi-language support for Tamil/English
  - `backend-search.php` and search helpers â€” location search autocomplete
  - `sweph/` library â€” Swiss Ephemeris for astrology calculations
- **Static Assets**
  - `css/` stylesheets for layout and chart display
  - `common.js`, `script.js` â€” shared client-side logic and UI interactions
  - External libraries: jQuery, Hammer.js, Font Awesome
- **Database**
  - `dsjv` MySQL database
  - Tables include user records, charts, announcements, world cities, activity log, etc.

## High-Level Architecture Diagram

````mermaid
flowchart TD
    Browser[Client Browser]
    subgraph Frontend
      Index[index.php]
    end
    subgraph AuthRouter
      AuthLogic[User/Cookie Detection]
      Route[Redirect to Feature Module]
    end
    subgraph Features
      Panchang[new_panchang.php]
      Birth[Birth_input.php]
      Matching[Matching_input.php]
      Jamakol[Jamakol_input.php]
      FreeUser[freeuser.php]
    end
    subgraph SharedBackend
      DB[db.php / db_config.php]
      Lib[lib.php]
      Lang[lang/lang.php + locale files]
      Search[backend-search.php]
      Ephemeris[sweph/ Swiss Ephemeris]
    end
    subgraph Assets
      CSS[css/*.css]
      JS[common.js / script.js]
      External[External libs: jQuery, Hammer.js]
    end
    Database[(MySQL dsjv)]

    Browser --> Index
    Index --> AuthLogic
    AuthLogic --> Route
    Route --> Panchang
    Route --> Birth
    Route --> Matching
    Route --> Jamakol
    Route --> FreeUser
    Panchang --> DB
    Birth --> DB
    Matching --> DB
    Jamakol --> DB
    FreeUser --> DB
    Panchang --> Lang
    Birth --> Lang
    Matching --> Lang
    Jamakol --> Lang
    FreeUser --> Lang
    Panchang --> Search
    Matching --> Search
    Panchang --> Ephemeris
    Birth --> Ephemeris
    Matching --> Ephemeris
    Jamakol --> Ephemeris
    Browser --> CSS
    Browser --> JS
    Browser --> External
    DB --> Database
```

## Notes

- `index.php` sets the initial session/cookie state and routes users by `user_type` constants.
- The site supports both mobile-specific and web-specific login flows.
- Many pages use a shared language engine and search-autocomplete.
- Astrology calculations rely on Swiss Ephemeris binaries under `sweph/`.

## Page-Level Navigation Flow

```mermaid
flowchart TD
    A[Start / Entry] -->|mobile or web| Index[index.php]
    Index --> FreeUser[freeuser.php]
    Index --> Login[new_loginPage.php]
    Index --> Reg[new_registration_mobile.php]
    Index --> Panchang[new_panchang.php]
    Index --> Birth[Birth_input.php]
    Index --> Matching[Matching_input.php]
    Index --> Jamakol[Jamakol_input.php]

    FreeUser -->|Register prompt| Reg
    FreeUser -->|Login link| Login
    Login -->|POST athenticate.php| Auth[Authentication]
    Auth -->|success| Index
    Auth -->|failure| Login

    Reg -->|POST registerToDB.php| RegisterDB[Registration saved]
    RegisterDB -->|success| Index

    Birth -->|Birth chart input| BirthSubmit[Birth_Chart.php]
    BirthSubmit -->|saved| BirthHistory[birthChartDisplay.php]
    BirthHistory -->|open record| BirthChart[Birth_Chart.php]
    BirthHistory -->|match option| MatchBirth[matchBirthChart.php]

    Matching -->|enter details| MatchingResult[matching results page]
    Jamakol -->|enter details| JamakolResult[jamakol result page]

    subgraph MobileFlow
      Reg
      Login
      Birth
      Matching
      Jamakol
    end

    subgraph SharedBackends
      Search[backend-search.php]
      DB[db.php / db_config.php]
      Lang[lang/lang.php]
    end

    FreeUser --> DB
    Login --> DB
    Reg --> DB
    Birth --> DB
    BirthHistory --> DB
    Matching --> DB
    Jamakol --> DB
    Search --> DB
    Index --> Lang
    Birth --> Lang
    Matching --> Lang
    Jamakol --> Lang
```

## Recommended Next Step

If you want, I can also map the database tables to these pages for a complete data flow diagram.
