# CHANGELOG

## 1.0.0-beta2

This release introduces one BC-breaking change from beta1: the TableEvents
methods have been renamed from (before|modify|after)(Insert|Update|Delete) to
append the word Row. For example, TableEvents::beforeUpate() is now
TableEvents::beforeUpdateRow(). If you have implemented these methods in your
custom TableEvents classes, you will need to change to the new names; the
signatures and logic remain otherwise identical. There is no effect on generated
classes.

There are new modify(Insert|Update|Delete) TableEvents methods that now apply
to insert(), update(), and delete() respectively. These allow you a chance to
modify the table-wide operation before working with the query object directly.

This release also adds a PHPStorm metadata resource, and updates the docs.

## 1.0.0-beta1

This release adds type-specific _TableSelect_ classes (to aid IDEs with return
typehint completion) and a new `Row::getArrayInit()` method.

## 1.0.0-alpha1

Initial release.
