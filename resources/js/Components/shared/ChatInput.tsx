import { useEffect, useRef, type KeyboardEvent, type Ref } from 'react';
import { Send } from 'lucide-react';
import { Button } from '@/Components/ui/button';

type ChatInputProps = {
  value: string;
  onChange: (value: string) => void;
  onSend: () => void;
  maxChars: number;
  disabled?: boolean;
  textareaRef?: Ref<HTMLTextAreaElement>;
};

const MAX_ROWS_PX = 128; // sekitar 4 baris

/**
 * CMP-007 (bagian input). Tumbuh hingga 4 baris; Enter kirim, Shift+Enter baris baru.
 * Enter saat komposisi IME tidak mengirim.
 */
export default function ChatInput({ value, onChange, onSend, maxChars, disabled = false, textareaRef }: ChatInputProps) {
  const innerRef = useRef<HTMLTextAreaElement | null>(null);
  const tooLong = value.length > maxChars;

  useEffect(() => {
    const el = innerRef.current;
    if (!el) {
      return;
    }
    el.style.height = 'auto';
    el.style.height = `${Math.min(el.scrollHeight, MAX_ROWS_PX)}px`;
  }, [value]);

  const onKeyDown = (event: KeyboardEvent<HTMLTextAreaElement>) => {
    if (event.key === 'Enter' && !event.shiftKey && !event.nativeEvent.isComposing) {
      event.preventDefault();
      if (!disabled) {
        onSend();
      }
    }
  };

  const setRefs = (node: HTMLTextAreaElement | null) => {
    innerRef.current = node;
    if (typeof textareaRef === 'function') {
      textareaRef(node);
    } else if (textareaRef) {
      (textareaRef as { current: HTMLTextAreaElement | null }).current = node;
    }
  };

  return (
    <div>
      <label htmlFor="chat-input" className="sr-only">
        Pesanmu
      </label>
      <div className="flex items-end gap-2">
        <textarea
          id="chat-input"
          ref={setRefs}
          rows={1}
          value={value}
          disabled={disabled}
          aria-invalid={tooLong}
          aria-describedby="chat-counter"
          placeholder="Tulis ceritamu di sini..."
          onChange={(event) => onChange(event.target.value)}
          onKeyDown={onKeyDown}
          className="min-h-11 flex-1 resize-none rounded-lg border-[1.5px] border-input bg-card px-3.5 py-2.5 text-base text-foreground placeholder:text-text-muted disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive"
        />
        <Button onClick={onSend} disabled={disabled || tooLong || value.trim() === ''} aria-label="Kirim pesan">
          <Send aria-hidden="true" />
          <span className="hidden sm:inline">Kirim</span>
        </Button>
      </div>
      <p id="chat-counter" className={`mt-1 text-right text-xs ${tooLong ? 'text-destructive' : 'text-text-muted'}`}>
        {value.length}/{maxChars} karakter
      </p>
    </div>
  );
}
