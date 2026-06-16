class StaffProfile {
  const StaffProfile({
    required this.username,
    required this.fullName,
    required this.firstName,
    required this.middleName,
    required this.lastName,
    required this.email,
    required this.avatar,
    required this.avatarUrl,
    required this.employeeNo,
    required this.position,
    required this.department,
    required this.dateHired,
    required this.tinNo,
    required this.gsisNo,
    required this.pagibigNo,
    required this.sssNo,
    required this.philHealthNo,
    required this.gender,
    required this.birthDate,
    required this.birthPlace,
    required this.bloodType,
    required this.maritalStatus,
    required this.height,
    required this.weight,
    required this.contactNo,
    required this.officialEmail,
    required this.address,
  });

  final String username;
  final String fullName;
  final String firstName;
  final String middleName;
  final String lastName;
  final String email;
  final String avatar;
  final String avatarUrl;

  final String employeeNo;
  final String position;
  final String department;
  final String dateHired;
  final String tinNo;
  final String gsisNo;
  final String pagibigNo;
  final String sssNo;
  final String philHealthNo;

  final String gender;
  final String birthDate;
  final String birthPlace;
  final String bloodType;
  final String maritalStatus;
  final String height;
  final String weight;

  final String contactNo;
  final String officialEmail;
  final String address;

  factory StaffProfile.fromJson(Map<String, dynamic> json) {
    String s(String k) => (json[k] ?? '').toString();
    return StaffProfile(
      username: s('username'),
      fullName: s('full_name'),
      firstName: s('first_name'),
      middleName: s('middle_name'),
      lastName: s('last_name'),
      email: s('email'),
      avatar: s('avatar'),
      avatarUrl: s('avatar_url'),
      employeeNo: s('employee_no'),
      position: s('position'),
      department: s('department'),
      dateHired: s('date_hired'),
      tinNo: s('tin_no'),
      gsisNo: s('gsis_no'),
      pagibigNo: s('pagibig_no'),
      sssNo: s('sss_no'),
      philHealthNo: s('philhealth_no'),
      gender: s('gender'),
      birthDate: s('birth_date'),
      birthPlace: s('birth_place'),
      bloodType: s('blood_type'),
      maritalStatus: s('marital_status'),
      height: s('height'),
      weight: s('weight'),
      contactNo: s('contact_no'),
      officialEmail: s('official_email'),
      address: s('address'),
    );
  }
}
