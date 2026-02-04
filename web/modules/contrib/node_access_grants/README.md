# Node Access Grants

This module makes it possible to add grants and access records using OOP.

All you need to do is create a tagged service that implements `NodeAccessGrantsInterface` and implement the two methods in the
interface like you would have done the relevant access grant hooks.

For example:

```
services:
  my_module.my_grants_implementation:
    class: 'Drupal\my_module\MyGrantsImplementation'
    tags:
      - { name: node_access_grants }
```
