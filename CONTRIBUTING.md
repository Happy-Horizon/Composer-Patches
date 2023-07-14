# Creating Patches:

## Patch name:
The structure for a patch name is in 3 parts:
* The patch number. In 4 digits with leading zero. These patch numbers are unique over all patches over all platforms/modules. Check [/patches/all](https://patches.happyhorizon.com/patches/all) to see the numbers currently in use
* The patch description, in lowercase, separated by underscores
* the `.patch` file extension

Example:
```
0448_fix_cropper_fileextension_issue.patch
```
  
Keep the total patch name length under 100 characters.

## Patch from open changes:

Create a copy of the file to patch as `<filename>.original`. Apply changes to the orignal (without the '.original' suffix).

Create a patch file:
```
git diff -U10 --no-index path/to/file.original path/to/file > patch_name.patch
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
git show 36a34960acf10e1be8e62f3c43d9d2efab90b680 -U10 > patch_name.patch
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
Magento commits use app/code in their repository for the file path, change this to the appropriate vendor folder in your patch

# Adding a new patch:

Create a PR on GitHub with:
- A clear description of the patch, and the issue it fixes  
  If available, a link to the relevant github issue
- The new patch added to the appropriate folder(s)

# Bug reports and patch/feature requests:
Please only create GitHub issues for the functionality of https://patches.happyhorizon.com/

This application is created to facilitate easy sharing of patches for the supported platforms/modules. It, and it's contributors, do not offer direct support for these.  
Check the original repositories for support

