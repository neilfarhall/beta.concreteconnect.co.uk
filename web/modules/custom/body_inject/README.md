How to use
-----------
1.) Create a block you want to insert into a node
2.) Create a profile and select the block, node type and conditions

What it does
--------------
Conditions:
- Checks if a Text has "more than/exactly/less than" x Parapgraphs
- AND/OR
- Checks if a Text has "more than/exactly/less than" Chars

- Then it inserts a block by actions, currently 3 avaliable:
  1 Offset to the middle position
  2 Insert the block after a number of paragraphs.
  3 Insert the block after a number of charcters.

ONLY set one value for the three above!

Info for the insert logic with characters
--------------------------
- Check char count and add after closest paragraph
- For character insertion. we can not insert after like 300 characters
- Because that could lead to insertion in the middle of a table or other weird places. So we use the closest paragraph

Problems
------------
-Plain HTML inserted?
Check the Textformat of you Adblock and set it to a custom textformat(create one) and disable all filters! Any filters! I advise creatin ga dedicated format because media filters etc. will mess up google adsense code for example somehow.
There is also still a problem where CKEditor doesn like EMPTY tags, so i added a &nbsp; inside the <ins> tag. It will result in creating <br> tags and output the html as plaintext...wtf.
Suggestions welcome.


Developer Info, Docs
-----------------
The Form for managing profiles is copied over from the "linkit" module and modified. Helped a lot, thank you.
This is relatively complex stuff, because of the split into multiple files and sensitive annotations.
If you want to understand it i suggest you read "Drupal 8 Development Cookbok" around pages 270!

Infos
https://www.drupal.org/docs/drupal-apis/configuration-api/creating-a-configuration-entity-type
https://drupalsun.com/ben/2015/05/08/creating-custom-config-entities-drupal-8
https://antistatique.net/en/we/blog/2018/05/01/drupal-8-how-to-translate-the-config-api
Drupal console can create a config entity!

Entity Blocks Infos
https://enzo.weknowinc.com/articles/2015/11/21/how-to-manipulate-an-entityblock-programmatically-in-drupal-8
https://www.drupal.org/node/2418529
https://www.webomelette.com/drupal-8-custom-data-configuration-entities-using-thirdpartysettingsinterface
some examples: https://gist.github.com/WengerK/5c7f3df9aa5c27a95cd8a29b72b28a19


