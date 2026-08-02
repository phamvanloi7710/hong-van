import { inject, Injectable } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { map, Observable } from 'rxjs';

import { MediaPickerContract, MediaPickerOptions, MediaPickerResult } from '../../core/media/media-picker.contract';
import { MediaPickerDialog } from './media-picker-dialog';

@Injectable()
export class MediaPickerService implements MediaPickerContract {
  private readonly dialog = inject(MatDialog);

  open(options: MediaPickerOptions = {}): Observable<MediaPickerResult | null> {
    return this.dialog.open<MediaPickerDialog, MediaPickerOptions, MediaPickerResult>(MediaPickerDialog, {
      data: options,
      width: '1040px',
      maxWidth: '96vw',
      maxHeight: '92vh',
    }).afterClosed().pipe(map((result) => result ?? null));
  }
}
