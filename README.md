# Everybody Codes solutions in PHP

## Requirements

- Everybody Codes account
- Docker & Docker Compose

## Installation

Clone the repository.

Put your session token under: `.env.local` file to be able to download inputs

`docker-compose up -d` then `docker-compose exec app composer install`

## Usage

Execute those inside the docker container:

### Generate class for solution for given event & quest
`./app generate 2024 2` - Generate for event 2023, quest 2

### Test and execute solutions
If year is not given (-y xxxx or --event xxxx), then it takes current year if we're in December, otherwise it takes the last available event

`./app solve 2024 1` - to execute quest 1 of 2024 event with Quest input (downloaded automatically)

`./app solve 2024 1 --test` - to execute solution for quest 1 with tests inputs (defined in the solution class)

`./app solve 2024 1 --validate` - validate already solved quest 1 event 2024, downloading the answers and checking the solution against them

---

This repo does follow the automation guidelines on the /r/adventofcode community wiki https://www.reddit.com/r/adventofcode/wiki/faqs/automation. Specifically:

- When all inputs and answers are available for given they - they are cached locally.
- If you suspect your input is corrupted, you can manually request a fresh copy by deleting the input file: `/inputs/{event}/{quest}`
- The User-Agent header in requests to AoC is set to me since I maintain this repo :)
