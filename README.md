# Happy Horizon Patches

A collection of patches for e-commerce platforms, and a Slim-PHP application to host them.

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
* The patch number. In 4 digits with leading zero. These patch numbers are unique over all patches over all platforms/modules. Check [/patches/all](https://patches.experius.nl/patches/all) to see the numbers currently in use
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

Patches will be hosted at https://patches.experius.nl/

# Creating composer.patches.json

On version pages (for example https://patches.experius.nl/patches/magento/2.4.6), it will be possible to automatically generate a `composer.patches.json` file for that version.

It's also possible to generate a `composer.patches.json` file for third party vendor patches (for example http://patches.experius.nl.local.xpdev.nl/patches/magento/third-party/smile).
There is (at the moment) no version information for these patches, so check manually if these apply to your project or not 

Sample output:
```
{
  "patches": {
    "magento/framework": {
      "448": "https://patches.experius.nl/patches/magento/2.4.5/framework/0448_fix_cropper_fileextension_issue.patch"
    },
    "magento/module-catalog": {
      "432": "https://patches.experius.nl/patches/magento/2.4.5/module-catalog/0432_toolbar_limiter_fix_for_empty_catalog_page_issue.patch",
      "438": "https://patches.experius.nl/patches/magento/2.4.5/module-catalog/0438_fixed_indexer_update_all.patch"
    },
    "magento/module-two-factor-auth": {
      "349": "https://patches.experius.nl/patches/magento/2.4.5/module-two-factor-auth/0349_fix_twofactor_2fa_blankscreen_layout.patch"
    },
    "magento/module-ui": {
      "433": "https://patches.experius.nl/patches/magento/2.4.5/module-ui/0433_magento_knockout_template_engine_sync_mod.patch"
    },
    "magento/module-url": {
      "555": "https://patches.experius.nl/patches/magento/2.4.5/module-url/0555_admin_reset_password_url_adds_admin_fix.patch"
    },
    "magento/module-user": {
      "444": "https://patches.experius.nl/patches/magento/2.4.5/module-user/0444_fix_blank_screen_layout_reset_forgotten_password.patch",
      "559": "https://patches.experius.nl/patches/magento/2.4.5/module-user/0559_admin_reset_password_link_always_expired.patch"
    }
  }
}
```

# External patches:

Patches can also be applied with `composer.patches.json` from other public urls and/or (relative) file path.

# Creating Patches:

## Patch from open changes:

Create a copy of the file to patch as `<filename>.original`. Apply changes to the orignal (without the '.original' suffix).

Create a patch file:
```
git diff -U10 --no-index path/to/file.original path/to/file > fixes_problem.patch
```
The -U10 is to create enough lines of context for the patch to be apply-able.

Notes:
- When patching code at the end of a file, make sure to remove `\No newline at end of file` from the context.
- When creating a file never exceed the character limit of 100 for the file name itself.

## Use PhpStorm to create a patch from open changes. This works even for files not being tracked by GIT

- Make the changes to the file you want to create the patch for.
- Right click the file in your directory tree. Select `Local History` -> `Show History`.
- Select the original version of the file and check if only changes you want to include in your patch are shown.
- Press the `Create Patch` button in the top left. This creates a patch file in the root of your project.
- Rename the patch as needed.

## Patch from Git commit:

### Command Line:

- To get commit hash, use `git log`.
- Create patch:
```
git show 36a34960acf10e1be8e62f3c43d9d2efab90b680 -U10 > fixes_problem.patch
```

### GitHub

Create a patch from a commit url by adding `.patch`

Before:
```
https://github.com/magento/magento2/commit/8a4e2d43f272c8f238eeb410c42b3756a021b8d0
```
After:
```
https://github.com/magento/magento2/commit/8a4e2d43f272c8f238eeb410c42b3756a021b8d0.patch
```

### Bitbucket

Create a patch from a commit url by adding `/raw`

Before:
```
https://bitbucket.org/experius/mage2-module-experius-patcher/commits/23ef51dcb529caa4b663c308fa042c923bde1ce2
```
After:
```
https://bitbucket.org/experius/mage2-module-experius-patcher/commits/23ef51dcb529caa4b663c308fa042c923bde1ce2/raw
```

**Note(!):**
Magento commits use app/code in their repository for the filepatch, change this to the appropriate vendor folder in your patch

# Contributer notes:

* To add a new patch, place it in the appropriate folder. If the folder for that version or module does not exist yet, please add it.
* If a new version is released, make a copy of the folder of the previous version, rework all patches that no longer apply or remove them. Check that the .install.flag is present in the new version folder.