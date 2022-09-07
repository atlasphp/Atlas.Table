# CHANGELOG

## 2.0.0 (UNRELEASED)

Initial release of the 2.x series.

To upgrade, see `docs/upgrading.md`.

Changes from the 1.x series include:

- _Row_ classes now retain columns as actual properties, not _$data_ array
  elements.

- The _IdentityMap_ classes from Atlas.Mapper have been moved into this package.

- Some exception messages have been expanded to be more informative.

- Row::init() is now Row::setLastAction()

- Row::getStatus() is now Row::getLastAction()

- Row::getAction() is now Row::getNextAction()
