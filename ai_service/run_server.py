#!/usr/bin/env python
"""Simple script to run the FastAPI app with uvicorn"""
import os
import sys

# Change to the directory where this script is located
script_dir = os.path.dirname(os.path.abspath(__file__))
os.chdir(script_dir)
sys.path.insert(0, '.')

# Load environment variables (for production deployment)
try:
    from dotenv import load_dotenv
    load_dotenv()
except ImportError:
    pass  # dotenv not required in development

from app import app
import uvicorn

if __name__ == "__main__":
    # Support dynamic port for cloud platforms (Railway, Render, etc.)
    host = os.getenv("AI_HOST", "0.0.0.0")
    port = int(os.getenv("AI_PORT", os.getenv("PORT", 5001)))

    print(f"Starting AI Service on {host}:{port}")
    uvicorn.run(app, host=host, port=port, log_level="warning")
