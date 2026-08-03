import { ChangeDetectionStrategy, Component, ElementRef, forwardRef, ViewChild } from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'hv-post-editor',
  imports: [MatButtonModule, MatIconModule],
  providers: [{ provide: NG_VALUE_ACCESSOR, useExisting: forwardRef(() => PostEditor), multi: true }],
  template: `
    <div class="toolbar" role="toolbar">
      <button mat-icon-button type="button" (click)="command('bold')"><mat-icon>format_bold</mat-icon></button>
      <button mat-icon-button type="button" (click)="command('italic')"><mat-icon>format_italic</mat-icon></button>
      <button mat-icon-button type="button" (click)="command('insertUnorderedList')"><mat-icon>format_list_bulleted</mat-icon></button>
      <button mat-icon-button type="button" (click)="command('formatBlock', 'h2')"><mat-icon>title</mat-icon></button>
      <button mat-icon-button type="button" (click)="createLink()"><mat-icon>link</mat-icon></button>
      <button mat-icon-button type="button" (click)="command('removeFormat')"><mat-icon>format_clear</mat-icon></button>
    </div>
    <div #editor class="editor" contenteditable="true" role="textbox" aria-multiline="true" (input)="changed()" (blur)="touched()"></div>
  `,
  styles: [`.toolbar{display:flex;gap:2px;border:1px solid #c7c7c7;border-bottom:0;border-radius:5px 5px 0 0;background:#f7f7f7}.editor{min-height:240px;padding:14px;border:1px solid #c7c7c7;border-radius:0 0 5px 5px;outline:none}.editor:focus{border-color:#3f51b5}`],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PostEditor implements ControlValueAccessor {
  @ViewChild('editor', { static: true }) private readonly editor!: ElementRef<HTMLDivElement>;
  private onChange: (value: string) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  writeValue(value: string | null): void { this.editor.nativeElement.innerHTML = value ?? ''; }
  registerOnChange(fn: (value: string) => void): void { this.onChange = fn; }
  registerOnTouched(fn: () => void): void { this.onTouched = fn; }
  setDisabledState(disabled: boolean): void { this.editor.nativeElement.contentEditable = String(!disabled); }
  touched(): void { this.onTouched(); }
  changed(): void { this.onChange(this.editor.nativeElement.innerHTML); }
  command(name: string, value?: string): void { this.editor.nativeElement.focus(); document.execCommand(name, false, value); this.changed(); }
  createLink(): void { const url = window.prompt('URL'); if (url && /^(https?:\/\/|\/|#)/i.test(url)) this.command('createLink', url); }
}
