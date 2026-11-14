

:dword PY_string(dword txt)
{
	dword l = strlen(txt);
	dword r = malloc(l+1);
	strlcpy(r, txt, l);
	RETURN r;
}

:dword PY_getChar(dword str, i)
{
	dword m = malloc(2);
	DSBYTE[m] = DSBYTE[str+i];
	RETURN m;
}
inline byte inteq(dword x, y)
{
	IF (x==y) RETURN 0xFF;
	RETURN 0;
}
inline cdecl dword PY_len(dword ARGC,...)
{
	dword r = 0;
	dword p = #ARGC;
	WHILE(ARGC)
	{
		p += 4;
		r += strlen(DSDWORD[p]);
		ARGC--;
	}
	RETURN r;
}

inline void freeAllocVariable(dword var)
{
	IF (var > #globalDataSectionEND) free(var);
}


:dword initConsole = 0;

inline cdecl void consoleInit(dword count,...)
{

	IF(!initConsole)
	{
		load_dll(libConsole, #con_init, 0);
		IF (count) con_init stdcall (-1, -1, -1, -1, DSDWORD[#count+4]);
		ELSE con_init stdcall (-1, -1, -1, -1, "Title");
		initConsole = 0xFF;
	}
}
inline dword powstr(dword text, i)
{
	dword position = 0;
	dword len = strlen(text);
	dword alloc = malloc(len*i+1);
	position = alloc;
	WHILE(i)
	{
		strcpy(position, text);
		position += len;
		i--;
	}
	RETURN alloc;
}
inline cdecl void PY_print(dword count,...)
{
	dword arg = #count;
	IF (!count) return;
	//consoleInit(0);
	load_dll(libConsole, #con_init, 0);
	con_init stdcall (-1, -1, -1, -1, "Title");
	return;
	WHILE(count)
	{
		count += 4;
		con_printf stdcall (DSDWORD[count]);
		count--;
		IF (count) con_printf stdcall (" ");
	}
	con_printf stdcall ("\n");
}

inline cdecl dword PY_input(dword ARGC,...)
{
	dword p = #ARGC;
	dword r = malloc(500);
	consoleInit(0);
	WHILE(ARGC)
	{
		p += 4;
		con_printf stdcall (DSDWORD[p]);
		ARGC--;
		IF (ARGC)con_printf stdcall (" ");
	}
	con_gets stdcall(r, 500);
	RETURN r;
}
inline byte intb(dword x, y)
{
	IF (x>y) RETURN 0xFF;
	RETURN 0;
}
inline void setStringLen(dword var, data)
{
	DSDWORD[var+OFFSET_STRING_LEN] = data;
}
inline void setStringData(dword var, data, l)
{
	dword position = 0;
	position = var+OFFSET_STRING_DATA;
	WHILE(l)
	{
		DSBYTE[position] = DSBYTE[data];
		position++;
		data++;
		l--;
	}
}
inline dword additionString(dword x, y)
{
	dword length = 0;
	dword newString = 0;
	dword position = 0;
	dword l1 = 0;
	dword l2 = 0;
	l1 = getStringLen(x);
	l2 = getStringLen(y);
	newString = malloc(l1+l2+OFFSET_STRING_DATA+1);
	setStringLen(newString, l1+l2);
	setStringData(newString, getStringData(x), l1);
	setStringData(newString+l1, getStringData(y), l2);
	RETURN newString;
}
inline dword fastcall getStringData(EAX){ RETURN EAX+OFFSET_STRING_DATA;}
inline dword fastcall getStringLen(EAX){ EAX = EAX+OFFSET_STRING_LEN; RETURN DSDWORD[EAX]; }
inline cdecl dword stdout(dword count,...)
{
	dword len = 0;
	dword arg = #count;
	dword data = 0;
	dword length = 0;
	WHILE(count)
	{
		arg += 4;
		data = getStringData(DSDWORD[arg]);
		length = getStringLen(DSDWORD[arg]);
		IF (arg == #count+4) CreateFile(length, data, "stdout");
		ELSE WriteFile(len, length, data, "stdout");
		len += length;
		count--;
		IF (count)
		{
			WriteFile(len, 1, " ", "stdout");
			len++;
		}
	}
}



inline cdecl dword PY_malloc(dword ARGC,...)
{
	dword p = #ARGC;
	p = DSDWORD[p+4];
	RETURN malloc(p);
}

inline cdecl dword PY_free(dword ARGC,...)
{
	dword p = #ARGC;
	dword i = 0;
	i = ARGC;
	WHILE(i)
	{
		p+=4;
		EAX = DSDWORD[p];
		free(EAX);
		DSDWORD[p] = 0;
		i--;
	}

}

inline cdecl dword PY_trim(dword ARGC,...)
{
	dword p = #ARGC;
	dword r = 0;
	dword r2 = 0;
	byte s = 0;
	byte cmd = 1;
	dword save = 0;
	dword l = strlen(p);
	p = DSDWORD[p+4];
	r = malloc(l+1);
	r2 = r;
	save = r;
	WHILE(DSBYTE[p])
	{
		s = DSBYTE[p];
		p++;
		IF (s == 13) || (s == 10) || (s == 8) || (s == ' ')
		{
			IF(cmd) CONTINUE;
		}
		ELSE save = r2;
		cmd = 0;
		DSBYTE[r2] = s;

		r2++;
	}
	DSBYTE[save+1] = 0;
	RETURN r;
}
