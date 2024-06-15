from fastapi import FastAPI, Request
import subprocess
import json
import logging

app = FastAPI()

# Konfigurasi logging
logging.basicConfig(level=logging.INFO)

@app.post("/run-script")
async def run_script(request: Request):
    data = await request.json()
    script_type = data.get("type")
    python_script = f"{script_type}.py"

    try:
        # Jalankan program Python dengan xvfb-run untuk display virtual
        result = subprocess.run(
            ["python3", python_script, json.dumps(data)], 
            capture_output=True, text=True, check=True
        )
        output = result.stdout.strip()
        
        try:
            response_data = json.loads(output)
        except json.JSONDecodeError as e:
            response_data = {"status": "error", "message": "Invalid JSON output from script", "output": output}

        # logging.info(f"Response Data: {response_data}")
        return response_data
    except subprocess.CalledProcessError as e:
        return {"status": "error", "error": e.stderr}
    except Exception as e:
        return {"status": "exception", "message": str(e)}
