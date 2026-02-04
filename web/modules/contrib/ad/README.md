# Summary

This module provides a flexible and extensible advertising system allowing
display ADs via blocks.

This AD system supports enabling multiple AD sources and tracking systems: by
default both AD data and AD tracking data are stored in the local database, but
alternative AD sources or trackers can be provided via plugins. For instance a
module could define a plugin to track AD events via Google Analytics.

The native `AD Tracker` module implements two trackers:
- The `Local AD event tracker` tracks impressions/clicks immediately, which on
  high traffic sites can result in significant additional load for the web
  servers.
- The `Queue-based local AD event tracker` tracks data via a queue, so events
  will only actually appear in the statistics display section after running
  cron or after manually processing `ad_track_queue`.


# Installation

Enable the main `AD` module, together with at least one AD source and one
tracker, typically the `AD Content` and `AD Tracker` modules.


# Usage

- After enabling all the required modules, configure a tracker for every enabled
AD source at `/admin/ad/settings`.
- Place one or more AD blocks as needed.
