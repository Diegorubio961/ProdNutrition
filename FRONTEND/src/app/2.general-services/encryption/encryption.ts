import { Injectable } from '@angular/core';
import * as CryptoJS from 'crypto-js';
import { encryption } from '../../../environtments/encryption';

@Injectable({
  providedIn: 'root',
})
export class Encryption {
  private readonly secretKey: string = encryption.secretKey;
  constructor() {}

  encrypt(data: any): string {
    try {
      // 1. Convertir el objeto JSON a texto (Stringify)
      const txt = JSON.stringify(data);
      
      // 2. Encriptar usando AES
      // Esto genera un formato estándar de Crypto-JS con Salt incluido
      const encrypted = CryptoJS.AES.encrypt(txt, this.secretKey).toString();
      
      return encrypted;
    } catch (e) {
      console.error('Error al encriptar:', e);
      return '';
    }
  }

  decrypt(ciphertext: string): any {
    try {
      // 1. Desencriptar los bytes
      const bytes = CryptoJS.AES.decrypt(ciphertext, this.secretKey);
      
      // 2. Convertir bytes a texto legible (UTF-8)
      const originalText = bytes.toString(CryptoJS.enc.Utf8);
      
      // 3. Si la cadena está vacía, es que la clave estaba mal o el texto corrupto
      if (!originalText) {
        return null;
      }

      // 4. Convertir el texto de vuelta a JSON (Parse)
      return JSON.parse(originalText);
    } catch (e) {
      console.error('Error al desencriptar. Posiblemente la llave no coincide.', e);
      return null;
    }
  }
}
