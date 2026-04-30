#!/usr/bin/env python3
"""
Scrape match data from ffhandball.fr championship pages.
Usage: python scrape_matches.py --url <championship_url> --output <output.json>
"""
import argparse
import json
import re
from typing import Optional

import scrapy
from scrapy.crawler import CrawlerProcess
from scrapy_playwright.page import PageMethod


# French month mapping for date parsing
FRENCH_MONTHS = {
    "janvier": "01", "février": "02", "mars": "03", "avril": "04",
    "mai": "05", "juin": "06", "juillet": "07", "août": "08",
    "septembre": "09", "octobre": "10", "novembre": "11", "décembre": "12",
}


def parse_french_date(date_str: str) -> Optional[str]:
    """Parse 'samedi 13 septembre 2025 à 20H30' -> '2025-09-13 20:30'"""
    match = re.search(
        r"(\d{1,2})\s+(\w+)\s+(\d{4})\s+à\s+(\d{1,2})H(\d{2})",
        date_str, re.IGNORECASE
    )
    if not match:
        return None

    day, month_name, year, hour, minute = match.groups()
    month = FRENCH_MONTHS.get(month_name.lower())
    if not month:
        return None

    return f"{year}-{month}-{int(day):02d} {int(hour):02d}:{minute}"


class FFHandballSpider(scrapy.Spider):
    name = "ffhandball"
    results = []

    custom_settings = {
        "DOWNLOAD_HANDLERS": {
            "http": "scrapy_playwright.handler.ScrapyPlaywrightDownloadHandler",
            "https": "scrapy_playwright.handler.ScrapyPlaywrightDownloadHandler",
        },
        "TWISTED_REACTOR": "twisted.internet.asyncioreactor.AsyncioSelectorReactor",
        "PLAYWRIGHT_BROWSER_TYPE": "chromium",
        "PLAYWRIGHT_LAUNCH_OPTIONS": {"headless": True},
        "LOG_LEVEL": "WARNING",
    }

    def __init__(self, url=None, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.start_urls = [url] if url else []

    def start_requests(self):
        for url in self.start_urls:
            yield scrapy.Request(
                url,
                meta={
                    "playwright": True,
                    "playwright_page_methods": [
                        PageMethod("wait_for_timeout", 5000),
                    ],
                },
                callback=self.parse,
            )

    def parse(self, response):
        # Get ALL text nodes from the page
        all_texts = response.css("::text").getall()
        all_texts = [t.strip() for t in all_texts if t.strip()]

        matches = []
        seen = set()
        i = 0

        while i < len(all_texts):
            # Look for "Journée X" pattern
            journee_match = re.match(r"Journée\s+(\d+)$", all_texts[i])
            if not journee_match:
                i += 1
                continue

            # Next text should be the date
            if i + 5 >= len(all_texts):
                i += 1
                continue

            date_parsed = parse_french_date(all_texts[i + 1])
            if not date_parsed:
                i += 1
                continue

            home_team = all_texts[i + 2]
            score_home = all_texts[i + 3]
            score_away = all_texts[i + 4]
            away_team = all_texts[i + 5]

            # Deduplicate
            key = f"{date_parsed}|{home_team}|{away_team}"
            if key in seen:
                i += 6
                continue
            seen.add(key)

            match_data = {
                "match_day": f"Journée {journee_match.group(1)}",
                "date": date_parsed,
                "home_team": home_team,
                "away_team": away_team,
            }

            if score_home != "-" and score_away != "-":
                try:
                    match_data["home_score"] = int(score_home)
                    match_data["away_score"] = int(score_away)
                    match_data["status"] = "played"
                except ValueError:
                    match_data["status"] = "upcoming"
            else:
                match_data["status"] = "upcoming"

            matches.append(match_data)
            i += 6

        FFHandballSpider.results = matches


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", required=True, help="Championship page URL")
    parser.add_argument("--output", required=True, help="Output JSON file path")
    args = parser.parse_args()

    FFHandballSpider.results = []

    process = CrawlerProcess()
    process.crawl(FFHandballSpider, url=args.url)
    process.start()

    with open(args.output, "w", encoding="utf-8") as f:
        json.dump(FFHandballSpider.results, f, ensure_ascii=False, indent=2)

    print(f"Scraped {len(FFHandballSpider.results)} items -> {args.output}")


if __name__ == "__main__":
    main()
