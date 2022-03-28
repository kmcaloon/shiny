
  

# shiny!

  

![enter image description here](https://drive.google.com/uc?export=view&id=1ebT8kODAE-BxnlRR82ufxK_3hvZmA7VF)

  

  

This is the personal starter framework that I use when starting new WordPress projects. It is a collection of code and patterns that I have built up over the years, including a custom scss/css framework that is sort of like the mutant child of Bootstrap and Tailwind. This repository and README are WIPs, and although this codebase is for personal use I figured I would give a quick breakdown of what is in here.
  

## 🎂 Features

  

- Performance first. Everything is built with the intention of being as modular and lightweight as possible for Lighthouse.

- Modern stack taking advantage of modern technology like PHP7+, next generation Javascript, etc.

- Component-based architecture similar to React organized along modified Atom design principles.

- Optimal image handling with lazyloading, automatic srcset and sizes, etc.

- Custom SCSS framework leveraging Bootstrap's Utility API to generate or convert utility classes similar to Tailwind's syntax.

- Lightweight vanilla JS set up and helper functions. Alpine.js highly recommended.

- Custom Gravity Forms frontend rendering for performance, and API endpoint for processing submissions.

- Frontend build tools with NPM/Yarn, Gulp, Webpack, PostCSS, and Babel.

- A general affinity for all things related to the legendary saga Firefly.

## Background
For some background, after spending a decent amount of time in the Jamstack ecosystem (primarily with NextJS and Gatsby) I started questioning whether it was everything I hoped and dreamed it would be. The DX was incredible, and it was fun playing around with all of the latest a greatest tools, but among other potential drawbacks I noticed that I wasn't seeing greatly improved Lighthouse scores for my projects. In fact, I started testing many of the popular sites and noticed that every single one had relatively poor Core Web Vitals on mobile, which had now become a ranking factor. This had me then questioning why exactly I was trying to replicate built-in browser capabilities with javascript, especially with the overhead it came with? For most of my projects this was unnecessary and the honest answer I came up with was that the main benefit I was seeing from this approach was an improved DX. It was simply more fun to build with. Since it came with some tradeoffs, this was an unacceptable answer.

Long story short, I migrated one of my main projects which is a somewhat involved ecommerce site using this framework. The result was an almost 80% improved mobile Lighthouse score. Since then I have seen similar results for other clients.

While obviously React frameworks are amazing tools and are great choices for certain kinds of projects, I have been very happy with these results along with trying to leverage constantly improving native browser functionality. If interested, I would highly recommend watching [Chris Ferdinandi - The Lean Web](https://www.youtube.com/watch?v=h5CnfIAUmrU)

  

## Overview

```
shiny/                               # → More info soon
├── dist/
├── functions.php
├── index.php
├── package.json
├── screenshot.png
├── lib/
│   ├── api/
|   │   ├── _api.php
│   │   └── API.php
│   ├── forms/
│   │   ├── _forms.php
│   │   └── TrackingAddOn.php
│   ├── integrations/
│   │   └── _integrations.php
│   ├── internal/
│   │   └── _internal.php
│   ├── marketing/
│   │   └── _marketing.php
│   ├── woocommerce/
│   │   └── _woocommerce.php
│   ├── acf.php
│   ├── admin.php
│   ├── assets.php
│   ├── block-editor.php
│   ├── dev.php
│   ├── extras.php
│   ├── settings.php
│   ├── setup.php
│   └── theme-wrapper.php
├── page-templates/
├── src/
│   ├── components/
│   │   ├── molecules/
│   │   ├── organisms/
│   │   ├── _components.php
|   |   └── Component.php
│   ├── fonts/
│   ├── img/
│   ├── js/
│   ├── pages/
│   ├── _abstracts.scss
│   ├── _bootstrap.scss
│   ├── _config.scss
│   ├── _icons.scss
│   ├── _layout.scss
│   ├── _type.scss
│   ├── _ui.scss
│   ├── _utils.scss
│   ├── global.scss
│   ├── admin.scss
│   ├── admin-head.js
│   ├── admin.js
│   ├── global.js
│   └── head-scripts.js
├── vendor/
├── .babelrc
├── .browserlistsrc
├── 404.php
├── admin-menu.json
├── base.php
├── composer.json
├── config.json
├── flex.php
├── functions.php
├── gulpfile.js
├── index.php
├── package.json
├── page.php
├── postcss.config
├── README.md
├── style.css
└── webpack.config.js
```

## Asset Handling

All of WordPress' default frontend assets are disabled by default. This includes jQuery which can be enqueued on a case-by-case basis if needbe. If for some reason you want jQuery to be enqueued globally, you can set "`KILL_JQUERY": false` within `config.json`.

  

**Stylesheets**

  

All `.scss` files prefixed with an underscore will be included within the global stylesheet. This stylesheet will be cached and served on every page so only critical styles should be included. This mostly includes resets, utility classes, fonts/icons, layout, and global UI. Non-critical component-specific styles should be placed within their component's directory with an non-underscored `.scss` file. During development, this processed stylesheet will be enqueued and served as a standard stylesheet. However, during production the minified styles will be printed within an internal `<style>` tag rendered above the first occurrence of the component within the HTML, which results in less requests and optimal processing.

  

All page-specific styles should be placed within `src/pages/`. They can be applied to any template file by calling the function `\Shiny\Assets\print_page_styles( $stylesheet_name )`. So if you have homepage-specific styles located in `src/pages/home.scss`, you can print them on the homepage by calling `\Shiny\Assets\print_page_styles( 'home' )`. The styles will be handled similar to component-specific styles.

  

**Javascript**

Javascript files are handled very similarly to stylesheets. All global scripts should be placed or imported within `src/global.js`. Component-specific scripts follow the same method as mentioned above.

  

## Link Prefetching

To automatically prefetch on page load, add a `data-prefetch-url` attribute to the anchor tag (no need for a value). In order to save resources, prefetching will be disabled for mobile devices without a 4g or 5g connection.

  

In addition, if you would like to automatically prefetch local links once they become visible within the viewport you can set `"PREFETCH_LOCAL_LINKS": true` with `config.json`.

  

## Responsive Images

**NOTE: For now requires the Advanced Custom Fields plugin**

To render responsive images with lazy loading capabilities, use the `Image` component located in `src/components/molecules/Image.php`. Basically it takes an ACF image array within its arguments, or builds one using `acf_get_attachment()`, and then generates the necessary `srcset` and `sizes` attributes. This component is a WIP and there are a few additional features, and for more information you can view the file.

  

## Components

All components extend React.Component -- 🤦 I mean \Shiny\Component -- which comes with its own set of methods to help with constructing and rendering components. Each component needs to be given a unique handle which is used as its class and file name, and must be placed within its own directory using this handle. Among other features, this allows for smooth autoloading without having to worry about requiring component files wherever you use them. For example, the Image component has `Image` as its handle, and is found within `src/components/molecules/Image/Image.php`. All component-specific styles or scripts must be placed within the same directory using the same handle.

  

Each component needs to have a `__construct` method which calls the parent's method with the following arguments `parent::__construct( {{ComponentHandle}}, __DIR__, $optional_args )`. The optional third argument is an array of arguments to pass to the component. For now the only two arguments are a `fields` argument which takes an associative array and sets properties for your component based on the key value pairs, and an `is_preview` argument for use within the Block Editor.

  

Finally, each component needs to have a `render()` method which outputs its HTML. It can be as flexible and use any parameters you may need (ie nested components or HTML), however it is suggested that most logic be delegated to either `__construct()` or other methods, as `render()` should serve as the component's primary view layer. To use component-specific css, use `$this->print_styles()` before rendering the HTML. For javascript, use `$this->print_scripts()`. Usually your scripts depend on the component's DOM elements, and if so the method should be placed after the HTML.

So for example, if you wanted to use the built-in image component this is how you would render it:

```
$image = new \Shiny\Components\Image( $args );
$image->render();

// Or in this case the render function accept optional class names.
$image->render( 'Block__element' ); 
```
  

## SCSS

TODO

  

## Javascript

TODO

  

## Admin

TODO

  

## Build Tools & Package Management

TODO

  

## Recommended Best Practices

TODO. Basically these sum up my recommended approach:

https://10up.github.io/Engineering-Best-Practices/

https://github.com/barrel/barrel-dev-best-practices