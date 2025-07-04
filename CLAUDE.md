# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a static HTML website for "Millenium Administración de Edificios y Condominios" (Millennium Building and Condominium Administration), a property management company based in Gran Concepción, Chile. The website is built using Bootstrap 5 and the Impact template from BootstrapMade.

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Bootstrap 5.3.3
- **Template**: Impact template from BootstrapMade
- **Backend**: PHP (for contact form processing)
- **Server Environment**: XAMPP (Apache/PHP)
- **Fonts**: Google Fonts (Roboto, Montserrat, Poppins)

## Project Structure

```
/
├── assets/
│   ├── css/
│   │   └── main.css          # Main stylesheet with custom styles
│   ├── img/                  # Images and assets
│   ├── js/
│   │   └── main.js           # Main JavaScript functionality
│   ├── scss/                 # SCSS source files (if any)
│   └── vendor/               # Third-party libraries
│       ├── bootstrap/        # Bootstrap framework
│       ├── bootstrap-icons/  # Bootstrap icons
│       ├── aos/              # Animate On Scroll library
│       ├── glightbox/        # Lightbox library
│       ├── swiper/           # Swiper slider library
│       └── php-email-form/   # PHP email form handler
├── forms/
│   └── contact.php           # Contact form handler
├── index.html                # Main homepage
├── beneficios.html           # Benefits page
├── service-details.html      # Service details page
└── corretaje.html            # Brokerage page
```

## Key Features

- **Responsive Design**: Bootstrap-based responsive layout
- **Single Page Application**: Main content in index.html with smooth scrolling navigation
- **Contact Form**: PHP-based contact form with email functionality
- **External Integration**: Links to Kastor property management system (tcel.cl)
- **WhatsApp Integration**: Fixed WhatsApp contact widget
- **Google Fonts**: Custom typography with Roboto, Montserrat, and Poppins

## Development Commands

This is a static website that doesn't require build tools. Development is done directly with HTML/CSS/JS files.

### Local Development
- Use XAMPP or similar local server environment
- Place files in htdocs directory
- Access via http://localhost/Millenium/

### Contact Form Setup
- Update `forms/contact.php` line 10 with actual receiving email address
- Configure SMTP settings if needed (lines 27-34)
- Ensure PHP Email Form library is available in vendor directory

## Color Scheme

The website uses a custom color palette defined in CSS variables:
- Background: #F9F6E6 (light cream)
- Primary text: #574964 (dark purple)
- Accent: #A5B68D (sage green)
- Surface: #ECDFCC (light beige)
- Hover: #DA8359 (orange)

## Content Management

- **Homepage**: Main company information and services overview
- **Services**: Detailed service descriptions with icons
- **About**: Company mission and vision
- **Contact**: Contact form and company details
- **FAQ**: Frequently asked questions section

## External Dependencies

All vendor libraries are included locally in the assets/vendor directory:
- Bootstrap 5.3.3
- Bootstrap Icons
- AOS (Animate On Scroll)
- GLightbox
- Swiper
- PureCounter
- Imagesloaded
- Isotope Layout

## Important Notes

- No build process required - direct file editing
- Contact form requires PHP server environment
- Images and assets are stored in assets/img/
- Template is based on BootstrapMade's Impact template
- Website is in Spanish language