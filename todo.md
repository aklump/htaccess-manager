## Blockers

## Critical

## Normal

## Backlog

- Change plugin discovery to not require_once files. Instead, discover class names without loading them (e.g., using token_get_all() to read namespace + class name), then let Composer autoload those classes normally.
  This is the “proper” long-term approach, but it’s a refactor of the plugin framework.

## Notes
