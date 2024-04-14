![GLPI_SAML_by_SpyK60](https://github.com/DonutsNL/glpisaml/assets/97617761/8beb0ec4-ec57-4ec6-8cd6-bc441bef34f4)

# glpi saml
This is a rewrite of the broadly used phpsaml plugin created by Derrick Smith. PHPSAML was written for GLPI predating version 10, and was maintained by Derrick on a best-effort basis. With new features, limitations and best-practices introduced into GLPI, the original plugin required more and more effort to maintain. Because of this I decided to rewrite the plugin and address these issues. 

Rewrite of phpSaml plugin by Derrick Smith with: PHP8 support, GLPI 10+ support, Using GLPI core objects where possible, PSR4 support, Concern based class structures, Focus on code readability, address SONAR LINTING issues, Improve community support, Composer support for updating 3rd party SAML and XML libraries, Introduce discord support channel, Support multiple IDPs, support rule based JIT usercreation.

# Status
PRERELEASE

# Current Focus
* Multiple IDP login
* JIT user creation WITH RULES
* Hardening the plugin

# Support
Want to support my work?
Star my repo and contribute to my stargazer achievement. 
Want to do more, I just love coffee: https://www.buymeacoffee.com/donutsnl

# Installation using composer
This plugin is also a registered composer package. This means you are able to download and update the plugin on stand alone GLPI installations using composer. In the future you prob want to use composer archive that will create a zip with glpisaml instead of require that will perform a full deployment.
On linux: run: 
- `apt-get install composer`
- `cd ~`
- `mkdir composer | cd composer`
- `composer require donutsnl/glpisaml v0.2.1-beta`
- `mv ./vendor/donutsnl/glpisaml [path_to_glpi_marketplace]`
- `rm -rf ./vendor`

# Installation git
[TODO]

# Installation marketplace
[TODO]

# Contribute, ideas and help?
Join my (and hopefully our in the future) discord at: [https://discord.gg/35tkHxHg](https://discord.gg/yKZB7VQUk6)
Have coding experience (or are learning to code) and want to add meaningfull changes and additions? First start from your own repository by forking this repository and then create pull requests. Deal with any feedback you receive and see your pullrequest being merged. If you have proven to be consistant, then request access to the repository as contributor and help me build a great tool for people to enjoy. Just want to share your idea, then please create an issue outlining the issue or your idea.

**Coding:**
- [Follow PSR where possible](https://www.php-fig.org/psr/)
- Use a decent IDE and consider using plugins like:
    -Code lenses (intelephense);
    -PSR4 compliant namespace resolver;
    -Composer integration;
    -Xdebug profiler;
    -SonarLint;
    -Twig language support;
    -tip: devsense PHP All-in-one.

# Credits
Special credits go to:
- Derrick Smith (creating the initial version PHPSaml)
- Raul, @gambware for their support to the OSS community (& buying me coffee), cheers!
- @MikeDevresse for providing fixes to the codebase.
- @SpyK-01 for licensing and sharing the logo via https://elements.envato.com/letter-shield-gradient-colorful-logo-XZ7LYCM.

# Get an idea where im going with this:
https://github.com/DonutsNL/phpsaml2/wiki/Plugin-Logic-and-Structure-Scratchboard
