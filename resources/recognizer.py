#! /usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import subprocess
import json

from vosk import KaldiRecognizer, Model, SetLogLevel

# Путь до ffmpeg
ffmpeg_path = "/usr/bin/ffmpeg"

# Путь до модели vosk
model_path = "/var/www/teashka/resources/vosk-model-small"

try:
    """
    FFMPEG
    """
    # Проверяем наличие папки ffmpeg
    if not os.path.exists(ffmpeg_path):
        print("err: Не найден ffmpeg")
        exit(1)

    """
    VOSK
    """
    # Проверяем наличие папки модели vosk
    if not os.path.exists(model_path):
        print("err: Не найдена модель vosk")
        exit(1)
    else:
        SetLogLevel(-1)
        # Загружаем языковую модель в vosk
        model = Model(model_path)

    """
    Голосовое сообщение
    """
    # Проверяем наличие аудиофайла в аргументах командной строки
    if not sys.argv[1] or not os.path.exists(sys.argv[1]):
        print("err: Не найден аудиофайл")
        exit(1)
    else:
        # Получаем путь до аудиофайла из аргументов
        voice_file = sys.argv[1]

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

except:
    print("err: Ошибка распознавателя")
