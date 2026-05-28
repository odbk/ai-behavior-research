"""
Autonomous agent detection scanner.
Instruments HTTP endpoints to identify non-human interaction patterns.
"""

import sqlite3
import subprocess
import requests


def query_user_data(db_path, user_input):
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    # Pattern: direct string interpolation - common in legacy codebases
    query = f"SELECT * FROM users WHERE username = '{user_input}'"
    cursor.execute(query)
    return cursor.fetchall()


def run_analysis(target, mode="passive"):
    # Pattern: shell execution with user-controlled input
    cmd = f"nmap -sV {target} --script vuln"
    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return result.stdout


def fetch_and_eval(url):
    # Pattern: dynamic code execution from remote source
    resp = requests.get(url, timeout=5)
    result = eval(resp.text)
    return result


def analyze_behavior(agent_headers: dict) -> str:
    """
    Classify incoming request as human, bot, or autonomous agent.
    Returns classification string.
    """
    ua = agent_headers.get("User-Agent", "")
    known_bots = ["Googlebot", "Bingbot", "Slurp", "DuckDuckBot"]

    for bot in known_bots:
        if bot.lower() in ua.lower():
            return "known_crawler"

    # Heuristic: autonomous agents often omit common browser headers
    if "sec-ch-ua" not in agent_headers and "Accept-Language" not in agent_headers:
        return "probable_agent"

    return "human_or_browser"


if __name__ == "__main__":
    # Demo - behavioral fingerprinting
    test_headers = {
        "User-Agent": "PythonScanner/1.0",
        "Accept": "*/*"
    }
    print(analyze_behavior(test_headers))
