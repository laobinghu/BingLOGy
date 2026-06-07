# Changelog

## [0.4.0](https://github.com/laobinghu/BingLOGy/compare/v0.3.0...v0.4.0) (2026-06-07)


### Features

* integrate Horizon, Pulse, Telescope into admin backend ([dc89661](https://github.com/laobinghu/BingLOGy/commit/dc89661b8fd0bc235f882f708efd38d7fc808d6b))


### Bug Fixes

* install ext-pcntl in Docker vendor stage for Horizon ([9999711](https://github.com/laobinghu/BingLOGy/commit/99997111c623eded73c214830f4cd706f9a6d19d))

## [0.2.0](https://github.com/laobinghu/BingLOGy/compare/v0.1.0...v0.2.0) (2026-06-07)


### Features

* add Docker, release-please, release & nightly workflows ([486cb4d](https://github.com/laobinghu/BingLOGy/commit/486cb4d0ea15c660b2846b5fdd83caaaa6db4338))


### Bug Fixes

* **ci:** checkout code before reading git SHA in nightly workflow ([bd7a5cb](https://github.com/laobinghu/BingLOGy/commit/bd7a5cb00bd071708e79e9bf52bb3f3968cf843c))
* **ci:** drop missing postcss.config.js from Dockerfile; exclude vendor & build from .dockerignore ([f258d75](https://github.com/laobinghu/BingLOGy/commit/f258d7592dfd003893e21ee6328b574ee94e9c0f))
* **ci:** provide vendor to frontend stage so CSS [@imports](https://github.com/imports) resolve ([9bf1f52](https://github.com/laobinghu/BingLOGy/commit/9bf1f523307933ae92e1818a24db333cfb606ba4))
