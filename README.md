# Happy Horizon Patches
A collection of patches for e-commerce platforms, and a Slim-PHP application to host them.

See the [GitHub repository](https://github.com/Happy-Horizon/Composer-Patches) for more information and contributing

**To be used with cweagans/composer-patches**

# Setup
Add cweagans/composer-patches to your project
```
composer require cweagans/composer-patches
```

Add the following snippet to the `extra` node in your project's `composer.json`
```
...
    "extra": {
        "composer-exit-on-patch-failure": true,
        "patches-file": "composer.patches.json"
    },
...
```

Create the file `composer.patches.json`, create the contents as described in [Creating composer.patches.json](#creating-composerpatchesjson)

Update the composer.lock hashes:
```
composer update --lock
```

All defined patches will now be applied every time the applicable modules are installed

# Patch Name:

The structure for a patch name is in 3 parts:
* The patch number. In 4 digits with leading zero. These patch numbers are unique over all patches over all platforms/modules. Check [/patches/all](https://patches.happyhorizon.dev/patches/all) to see the numbers currently in use
* The patch description, in lowercase, separated by underscores
* the `.patch` file extension

Example:
```
0448_fix_cropper_fileextension_issue.patch
```


# Patch File Structure:

All patch files are grouped as:
* platform (for example `magento`) 
    * version (for example `2.4.5`)
        * module (for example `module-catalog`)

Patched versions such as Magento 2.4.5-p1 are considered different versions, since some patches for 2.4.5 may not apply.

Every version should contain all applicable patches, even if this results in file duplication.  
This way, the version folder also serves as comprehensive documentation for all known patches for that version.

A `.<platform>.flag` file should be included in the version folder to facilitate [generating the `composer.patches.json` file](#creating-composerpatchesjson)

### Third Party patches per platform:

Besides patches for the platform versions, patches for frequently used third party modules can also be included.

These are grouped as:
* platform (for example `magento`)
    * vendor (for example `smile`)
        * module (for example `elasticsuite`)

A `.<vendor>.flag` file should be included in the vendor folder to facilitate [generating the `composer.patches.json` file](#creating-composerpatchesjson)


# Browsing patches:

Patches will be hosted at https://patches.happyhorizon.dev/

# Creating composer.patches.json

On version pages (for example https://patches.happyhorizon.dev/patches/magento/2.4.6), it will be possible to automatically generate a `composer.patches.json` file for that version.

It's also possible to generate a `composer.patches.json` file for third party vendor patches (for example http://patches.happyhorizon.dev/patches/magento/third-party/smile).  
There is (at the moment) no version information for these patches, so check manually if these apply to your project or not 

Sample output:
```
{
  "patches": {
    "magento/framework": {
      "448": "https://patches.happyhorizon.dev/patches/magento/2.4.5/framework/0448_fix_cropper_fileextension_issue.patch"
    },
    "magento/module-catalog": {
      "432": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-catalog/0432_toolbar_limiter_fix_for_empty_catalog_page_issue.patch",
      "438": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-catalog/0438_fixed_indexer_update_all.patch"
    },
    "magento/module-two-factor-auth": {
      "349": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-two-factor-auth/0349_fix_twofactor_2fa_blankscreen_layout.patch"
    },
    "magento/module-ui": {
      "433": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-ui/0433_magento_knockout_template_engine_sync_mod.patch"
    },
    "magento/module-url": {
      "555": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-url/0555_admin_reset_password_url_adds_admin_fix.patch"
    },
    "magento/module-user": {
      "444": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-user/0444_fix_blank_screen_layout_reset_forgotten_password.patch",
      "559": "https://patches.happyhorizon.dev/patches/magento/2.4.5/module-user/0559_admin_reset_password_link_always_expired.patch"
    }
  }
}
```

# External patches:

Patches can also be applied with `composer.patches.json` from other public urls and/or (relative) file path.
