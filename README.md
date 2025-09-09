# JetEngine - Single page for Custom Content Type

Allows to create a custom single page for the CCT item based on the Listing Item template.

## Usecase and Workflow

JetEngine CCTs allows you to create an optimized way to store the data in the WP database - it stores all the data in the single table.

With JetEngine Listings, you can output this data in format of list, grid, carousel, table etc. The only limitation - out of the box CCTs doesn't have a single page, similar to WP native CPTs.

This plugin allows to remove this limitation. It allows you to select what CCTs could have a single page and how will look like the URL of this page. Then, in the Listing Grid Item for given CCT you can add a link to this single page to allow user (or search engines) to visit it. Done!

## Getting started

- Download, install and activate this plugin as regular WP plugin;
- Go to the **CCT Single Page** admin menu page;
- Setup single page config for the CCTs you need;
- Add an URL to the single page to the CCT listing.

## Setting up the single page config

On the **CCT Single Page** you can setup configuration for the different CCTs created at your website. To start setup of your 1st config - click "Add new" button

<img width="1026" height="179" alt="image" src="https://github.com/user-attachments/assets/f81c8c43-80ed-4421-bbd1-4a11697e3ae4" />

Each setup includes the next settings:

<img width="1009" height="759" alt="image" src="https://github.com/user-attachments/assets/80021eaa-2879-4ffd-a9ad-4f6b7ade8421" />


- **Rewrite base** - Defines the part of the URL between your home url and actual CCT slug.
<img width="1400" height="80" alt="image" src="https://github.com/user-attachments/assets/08088ae8-2e0b-4c88-8a3e-9546036a00c1" />

- **Content Type** - CCT to add a single page for.
- **Content template** - Listing Item template, which will be used as main content template for the single page. You can use any builders supported by JetEngine to create it.
- **Slug field** - CCT field, which will be used to generate a slug of the current CCT item for the URL. You can use or specially defined field of your CCT, which is already formatted for the URL. Or any other **text** field of your CCT - plugin automatically formats it as URL slug.
<img width="1538" height="74" alt="image" src="https://github.com/user-attachments/assets/9d1b92de-fb3d-4f20-8f82-b9e9b4ff7543" />

- **Page title pattern** - format of the page title, which will be generated for the current CCT item. Use fields of 
- **Meta description pattern** - format of the meta description for the page.

For the last 2 fields you can use any CCT fields in `%field_name%` format. For the **Slug field** field use plain `field_name`, without `%`.

For **Page title pattern** and **Meta description pattern** you can use plain array fields - they are will be imploded into the string.

## Adding a single page URL into the CCT listing

- Using Dynamic Link widget
<img width="428" height="354" alt="image" src="https://github.com/user-attachments/assets/e88f142b-e467-4a39-ae13-a571abda0471" />

- Using linked image in the Dynamic Image widget
<img width="393" height="286" alt="image" src="https://github.com/user-attachments/assets/75861b36-3cdc-4671-89b5-5144b96d9fe9" />

- Using a filter callback (mostly for Timber/Twig listings)
<img width="469" height="291" alt="image" src="https://github.com/user-attachments/assets/e814d796-aae0-4d12-9652-3605e869d727" />
