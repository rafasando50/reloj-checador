import { defineConfig } from 'astro/config';

// Eliminamos el import de Node ya que BanaHosting no lo usará así
export default defineConfig({
  output: 'static', // Cambiamos de 'server' a 'static'
  build: {
    format: 'file'
  },
  // El adapter se quita o se comenta porque ya no procesarás en el servidor
  security: {
    checkOrigin: false
  },
  vite: {
    server: {
      allowedHosts: ['borrowing-anvil-regular.ngrok-free.dev']
    }
  }
});