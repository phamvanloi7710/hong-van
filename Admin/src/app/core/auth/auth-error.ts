import { HttpErrorResponse } from '@angular/common/http';

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

export function authErrorMessage(error: unknown, fallback: string): string {
  if (!(error instanceof HttpErrorResponse) || !isRecord(error.error)) {
    return fallback;
  }

  const errors = error.error['errors'];

  if (isRecord(errors)) {
    for (const messages of Object.values(errors)) {
      if (Array.isArray(messages) && typeof messages[0] === 'string') {
        return messages[0];
      }
    }
  }

  return typeof error.error['message'] === 'string' ? error.error['message'] : fallback;
}
