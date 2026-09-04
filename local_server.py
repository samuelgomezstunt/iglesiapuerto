import http.server
import socketserver
import os
import sys

PORT = 8000
DIRECTORY = os.path.dirname(os.path.abspath(__file__))

class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=DIRECTORY, **kwargs)

    def translate_path(self, path):
        # Strip /iglesiapuerto prefix for local preview
        if path.startswith('/iglesiapuerto'):
            path = path[len('/iglesiapuerto'):]
            if not path:
                path = '/'
        return super().translate_path(path)

print(f"\n=======================================================")
print(f" Servidor de prueba local para Iglesia Web Estatica")
print(f" Visita: http://localhost:{PORT}/iglesiapuerto/")
print(f" Presiona Ctrl+C para detener")
print(f"=======================================================\n")

with socketserver.TCPServer(("", PORT), Handler) as httpd:
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nServidor detenido.")
