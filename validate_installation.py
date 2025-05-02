import sys
import face_recognition
import face_recognition_models

print("=== VALIDATION REPORT ===")
print(f"Python executable: {sys.executable}")
print(f"Python path: {sys.path}")

print("\n=== MODEL PATHS ===")
print(f"Face model: {face_recognition_models.face_recognition_model_location()}")
print(f"Landmarks: {face_recognition_models.shape_predictor_5_face_landmarks_location()}")

print("\n=== TESTING FACE DETECTION ===")
image = face_recognition.load_image_file("test_image.jpg")
face_locations = face_recognition.face_locations(image)
print(f"Found {len(face_locations)} face(s) in test image")
