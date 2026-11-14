
inline dword malloc(dword size)
{
	$mov	 eax, 68
	$mov	 ebx, 12
	$mov	 ecx, size
	$int	 0x40;
}

inline dword free(dword ptr)
{
	$mov	 eax, 68
	$mov	 ebx, 13
	$mov	 ecx, ptr
	$int	 0x40;
}

inline dword realloc(dword mptr, size)
{
	$mov     eax, 68
	$mov     ebx, 20
	$mov     ecx, size
	$mov     edx, mptr
	$int     0x40
	return   EAX;
}
