![GLPI_SAML_by_SpyK60](https://github.com/DonutsNL/glpisaml/assets/97617761/8beb0ec4-ec57-4ec6-8cd6-bc441bef34f4)

# glpi saml
This is a rewrite of the broadly used phpsaml plugin created by Derrick Smith. PHPSAML was written for GLPI predating version 10, and was maintained by Derrick on a best-effort basis. With new features, limitations and best-practices introduced into GLPI, the original plugin required more and more effort to maintain. Because of this I decided to rewrite the plugin and address these issues. 

Rewrite of phpSaml plugin by Derrick Smith with: PHP8 support, GLPI 10+ support, Using GLPI core objects where possible, PSR4 support, Concern based class structures, Focus on code readability, address SONAR LINTING issues, Improve community support, Composer support for updating 3rd party SAML and XML libraries, Introduce discord support channel, Support multiple IDPs, support rule based JIT usercreation.

# Support
Want to support the work and focus?
Star my repo, and I just love coffee: https://www.buymeacoffee.com/donutsnl


# Contribute, ideas and help?
Join our discord at: [https://discord.gg/35tkHxHg](https://discord.gg/yKZB7VQUk6)

Collaboration and diverse minds help create great tools. Want to contribute, you are more than welcome.**Got coding experience**: ask in our discord to be added to the project as collaborator, assign a issue, create a fork, add your code or improvements, create a pullrequest. **No coding experience**: ask in our discord to be added to the project as collaborator, download the latest (not yet functional) version and participate in testing and providing early feedback or help me write user manuals on our wiki collaborator role. **want to learn coding** Reach out on our discord. We will find you a 'good first story' to start working on and help you develop those skills.

**If you want to code, test or write:**
- Make sure you download the plugin into the 'GLPI_HOME\marketplace\glpisaml\' folder.
- Make sure not to use a production environment for testing!
- Please create issues if it concerns the code or functionality of the plugin.
- Only code whats in the issue assigned to you.
- [Follow PSR where possible](https://www.php-fig.org/psr/).
- Uncertain as a coder? Dont worry, just code, follow the feedback, watch you PR being merged.
- Please use discord for all other stuff.
- Contributed, but not 'yet' mentioned in our hall of fame, leave a PM in discord.

# Current focus
- harden the configuration objects;
- implement loginpage;
- start building the ACS;

# Credits
Special credits go to:
- Derrick Smith (creating the initial version PHPSaml)
- Raul, @gambware for their support to the OSS community (& buying me coffee), cheers!
- @MikeDevresse for providing fixes to the codebase.
- @SpyK-01 for licensing and sharing the logo via https://elements.envato.com/letter-shield-gradient-colorful-logo-XZ7LYCM.

# Get an idea where im going with this:
https://github.com/DonutsNL/phpsaml2/wiki/Plugin-Logic-and-Structure-Scratchboard
