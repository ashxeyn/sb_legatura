#!/usr/bin/env python
"""Simple script to run the FastAPI app with uvicorn"""
import os
import sys

# Change to the directory where this script is located
script_dir = os.path.dirname(os.path.abspath(__file__))
os.chdir(script_dir)
sys.path.insert(0, '.')

from app import app
import uvicorn

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=5001, log_level="warning")
