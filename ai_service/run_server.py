#!/usr/bin/env python
"""Simple script to run the FastAPI app with uvicorn"""
import os
import sys

# Change to ai_service directory
os.chdir('c:\\Users\\user\\Documents\\GitHub\\sb_legatura\\ai_service')
sys.path.insert(0, '.')

from app import app
import uvicorn

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=5001, log_level="warning")
