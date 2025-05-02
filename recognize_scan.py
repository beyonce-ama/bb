#!/usr/bin/env python3
import sys
import os

# Modified output handling for Windows compatibility
if sys.platform == 'win32':
    # Windows-specific unbuffered output setup
    import io
    sys.stdout = io.TextIOWrapper(open(sys.stdout.fileno(), 'wb', 0), write_through=True)
    sys.stderr = io.TextIOWrapper(open(sys.stderr.fileno(), 'wb', 0), write_through=True)
else:
    # Unix-like systems can use traditional unbuffered
    sys.stdout = os.fdopen(sys.stdout.fileno(), 'w', 0)
    sys.stderr = os.fdopen(sys.stderr.fileno(), 'w', 0)

print("Python script started", flush=True)
print(f"Python version: {sys.version}", flush=True)
print(f"Working directory: {os.getcwd()}", flush=True)
print(f"Script location: {__file__}", flush=True)
print(f"Arguments: {sys.argv}", flush=True)

import json
import traceback
import cv2
import numpy as np
import dlib
from datetime import datetime
from mysql.connector import connect, Error

# ===== MANUAL MODEL CONFIGURATION =====
MODEL_DIR = r"C:\face_recognition_models\models"

# Verify models exist
required_models = {
    "face_recognition": "dlib_face_recognition_resnet_model_v1.dat",
    "landmarks": "shape_predictor_5_face_landmarks.dat"
}

for name, model in required_models.items():
    model_path = os.path.join(MODEL_DIR, model)
    if not os.path.exists(model_path):
        error_msg = {
            "success": False,
            "message": f"Missing {name} model at {model_path}",
            "timestamp": datetime.now().isoformat(),
            "available_files": os.listdir(MODEL_DIR) if os.path.exists(MODEL_DIR) else "Directory not found"
        }
        print(json.dumps(error_msg), file=sys.stderr, flush=True)
        sys.exit(1)

# Initialize dlib models with error handling
try:
    face_detector = dlib.get_frontal_face_detector()
    landmark_predictor = dlib.shape_predictor(os.path.join(MODEL_DIR, required_models["landmarks"]))
    face_encoder = dlib.face_recognition_model_v1(os.path.join(MODEL_DIR, required_models["face_recognition"]))
except Exception as e:
    error_msg = {
        "success": False,
        "message": f"Failed to initialize dlib models: {str(e)}",
        "error_type": type(e).__name__,
        "timestamp": datetime.now().isoformat()
    }
    print(json.dumps(error_msg), file=sys.stderr, flush=True)
    sys.exit(1)

def face_locations(img):
    """Custom face detection function"""
    return face_detector(img, 1)

def face_encodings(img, locations):
    """Custom face encoding function"""
    shapes = [landmark_predictor(img, location) for location in locations]
    return [np.array(face_encoder.compute_face_descriptor(img, shape, 1)) for shape in shapes]

def face_distance(face_encodings, face_to_compare):
    """Custom face distance calculation"""
    if len(face_encodings) == 0:
        return np.empty(0)
    return np.linalg.norm(face_encodings - face_to_compare, axis=1)
# ===== END MANUAL CONFIGURATION =====

def log_error(message):
    error_data = {
        "success": False,
        "message": str(message),
        "timestamp": datetime.now().isoformat(),
        "error_type": type(message).__name__,
        "traceback": traceback.format_exc()
    }
    print(json.dumps(error_data), file=sys.stderr)
    sys.stderr.flush()

def load_image_from_bytes(photo_bytes):
    """Load image from binary data stored in database"""
    try:
        nparr = np.frombuffer(photo_bytes, np.uint8)
        if nparr.size == 0:
            return None
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        return cv2.cvtColor(image, cv2.COLOR_BGR2RGB)  # Convert to RGB
    except Exception as e:
        log_error(f"Error loading image from bytes: {str(e)}")
        return None

def get_employee_encodings():
    """Retrieve employee face encodings from database"""
    try:
        connection = connect(
            host='localhost',
            database='attdb',
            user='root',
            password='',
            connect_timeout=5
        )
        
        cursor = connection.cursor(dictionary=True)
        cursor.execute("""
            SELECT registration_number, photo, photo1, photo2, photo3, photo4 
            FROM employees 
            WHERE photo IS NOT NULL
        """)
        
        employees = cursor.fetchall()
        encodings = {}
        
        for emp in employees:
            for i in range(5):
                photo_field = 'photo' + ('' if i == 0 else str(i))
                if not emp.get(photo_field):
                    continue
                    
                try:
                    rgb_image = load_image_from_bytes(emp[photo_field])
                    if rgb_image is None:
                        continue
                        
                    locations = face_locations(rgb_image)
                    if not locations:
                        continue
                        
                    encodings_list = face_encodings(rgb_image, locations)
                    if encodings_list:
                        encodings[emp['registration_number']] = encodings_list[0]
                        break
                        
                except Exception as e:
                    log_error(f"Error processing {photo_field}: {str(e)}")
                    continue
        
        return encodings
        
    except Error as e:
        log_error(f"Database error: {str(e)}")
        return None
    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

def main():
    try:
        if len(sys.argv) < 2:
            log_error("No image file provided")
            sys.exit(1)

        upload_image_path = sys.argv[1]

        # Verify image file exists
        if not os.path.exists(upload_image_path):
            log_error(f"Image file not found: {upload_image_path}")
            sys.exit(1)

        # Load and process image
        try:
            # Read image using OpenCV
            unknown_image = cv2.imread(upload_image_path)
            if unknown_image is None:
                log_error("Could not read image file")
                sys.exit(1)
                
            rgb_unknown = cv2.cvtColor(unknown_image, cv2.COLOR_BGR2RGB)
            
            # Detect faces
            locations = face_locations(rgb_unknown)
            if not locations:
                log_error("No face detected in the image")
                sys.exit(1)
                
            if len(locations) > 1:
                log_error("Multiple faces detected - only one face allowed")
                sys.exit(1)

            # Get face encoding
            unknown_encoding = face_encodings(rgb_unknown, locations)[0]
            
            # Get employee encodings from database
            encodings = get_employee_encodings()
            if encodings is None or not encodings:
                log_error("No employee encodings found in database")
                sys.exit(1)
                
            # Find best match
            best_match = None
            best_distance = 0.6  # Threshold for face matching
            best_confidence = 0
            
            for reg_num, known_encoding in encodings.items():
                distance = face_distance([known_encoding], unknown_encoding)[0]
                if distance < best_distance:
                    best_distance = distance
                    best_match = reg_num
                    best_confidence = 1 - distance
            
            if best_match:
                result = {
                    "success": True, 
                    "registration_number": best_match,
                    "confidence": best_confidence,
                    "timestamp": datetime.now().isoformat()
                }
                print(json.dumps(result))
                sys.exit(0)
            else:
                log_error("No matching employee found (best distance: {:.2f})".format(best_distance))
                sys.exit(1)
                
        except Exception as e:
            log_error(f"Face processing error: {str(e)}")
            sys.exit(1)
            
    except Exception as e:
        error_msg = {
            "success": False,
            "message": f"Unexpected error: {str(e)}",
            "error_type": type(e).__name__,
            "traceback": traceback.format_exc(),
            "timestamp": datetime.now().isoformat()
        }
        print(json.dumps(error_msg), file=sys.stderr, flush=True)
        sys.exit(1)

if __name__ == "__main__":
    main()