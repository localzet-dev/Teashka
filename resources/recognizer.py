#! /usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import subprocess
import json

from vosk import KaldiRecognizer, Model, SetLogLevel

# Путь до ffmpeg
ffmpeg_path = "/usr/bin/ffmpeg"

def check_path(path, error_message):
    if not os.path.exists(path):
        print(f"err: {error_message}")
        exit(1)

def recognize_voice(voice_file, model_path):
    # Конвертируем аудио в формат wav и получаем результат в process.stdout
    process = subprocess.Popen(
        [
            ffmpeg_path,
            "-loglevel", "quiet",
            "-i", voice_file,
            "-ar", "16000",
            "-ac", "1",
            "-f", "s16le",
            "-"
        ],
        stdout=subprocess.PIPE
    )

    # Создаем распознаватель с использованием модели
    offline_recognizer = KaldiRecognizer(model, 16000)
    offline_recognizer.SetWords(True)

    # Читаем и распознаем данные по частям
    while True:
        data = process.stdout.read(4000)
        if len(data) == 0:
            break
        if offline_recognizer.AcceptWaveform(data):
            pass

    # Возвращаем распознанный текст в виде str
    result_json = offline_recognizer.FinalResult()
    result_dict = json.loads(result_json)
    print("suc: " + result_dict["text"])

try:
    """
    FFMPEG
    """
    check_path(ffmpeg_path, "Не найден ffmpeg")

    """
    VOSK
    """
    if len(sys.argv) < 3:
        print("err: Не указан путь к модели")
        exit(1)

    model_path = sys.argv[2]
    check_path(model_path, "Не найдена модель vosk")
    SetLogLevel(-1)
    model = Model(model_path)

    """
    Голосовое сообщение
    """
    if len(sys.argv) < 2:
        print("err: Не найден аудиофайл")
        exit(1)

    voice_file = sys.argv[1]
    check_path(voice_file, "Не найден аудиофайл")

    recognize_voice(voice_file, model_path)

except Exception as e:
    print(f"err: Ошибка распознавателя - {str(e)}")
